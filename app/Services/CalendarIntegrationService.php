<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Booking;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Support\Facades\Log;

class CalendarIntegrationService
{
    protected $client;

    protected $calendarService;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Initialize Google API Client
     */
    protected function initializeClient(): void
    {
        $this->client = new Client;
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes([
            Calendar::CALENDAR,
            Calendar::CALENDAR_EVENTS,
            Calendar::CALENDAR_READONLY,
        ]);
        $this->client->setAuthConfig(storage_path('app/google/calendar-credentials.json'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->calendarService = new Calendar($this->client);
    }

    /**
     * Set access token for authenticated user
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->client->setAccessToken($accessToken);
    }

    /**
     * Get authorization URL for OAuth
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $authCode): array
    {
        return $this->client->fetchAccessTokenWithAuthCode($authCode);
    }

    /**
     * Sync booking to Google Calendar
     */
    public function syncBookingToCalendar(Booking $booking): ?Event
    {
        try {
            if (! $this->client->getAccessToken()) {
                Log::warning('Google Calendar: No access token available');

                return null;
            }

            $event = new Event([
                'summary' => "Booking: {$booking->guest_name}",
                'description' => $this->generateBookingDescription($booking),
                'start' => [
                    'dateTime' => $booking->check_in->toIso8601String(),
                    'timeZone' => config('app.timezone', 'Asia/Jakarta'),
                ],
                'end' => [
                    'dateTime' => $booking->check_out->toIso8601String(),
                    'timeZone' => config('app.timezone', 'Asia/Jakarta'),
                ],
                'location' => $booking->roomType->name ?? 'Hotel',
                'attendees' => $this->getBookingAttendees($booking),
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60], // 1 day before
                        ['method' => 'popup', 'minutes' => 60], // 1 hour before
                    ],
                ],
                'extendedProperties' => [
                    'private' => [
                        'bookingId' => $booking->id,
                        'tenantId' => $booking->tenant_id ?? 'unknown',
                        'source' => 'qalcuity-erp',
                    ],
                ],
            ]);

            $calendarId = 'primary';
            $createdEvent = $this->calendarService->events->insert($calendarId, $event);

            // Save Google Calendar event ID to booking
            $booking->update([
                'google_calendar_event_id' => $createdEvent->id,
            ]);

            Log::info("Booking {$booking->id} synced to Google Calendar: {$createdEvent->id}");

            return $createdEvent;
        } catch (\Exception $e) {
            Log::error("Failed to sync booking to Google Calendar: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Update booking in Google Calendar
     */
    public function updateBookingInCalendar(Booking $booking): ?Event
    {
        try {
            if (! $booking->google_calendar_event_id) {
                return $this->syncBookingToCalendar($booking);
            }

            $calendarId = 'primary';
            $event = $this->calendarService->events->get($calendarId, $booking->google_calendar_event_id);

            $event->setSummary("Booking: {$booking->guest_name}");
            $event->setDescription($this->generateBookingDescription($booking));
            $event->start->setDateTime($booking->check_in->toIso8601String());
            $event->end->setDateTime($booking->check_out->toIso8601String());

            $updatedEvent = $this->calendarService->events->update(
                $calendarId,
                $booking->google_calendar_event_id,
                $event
            );

            Log::info("Booking {$booking->id} updated in Google Calendar");

            return $updatedEvent;
        } catch (\Exception $e) {
            Log::error("Failed to update booking in Google Calendar: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Delete booking from Google Calendar
     */
    public function deleteBookingFromCalendar(Booking $booking): bool
    {
        try {
            if (! $booking->google_calendar_event_id) {
                return true;
            }

            $calendarId = 'primary';
            $this->calendarService->events->delete($calendarId, $booking->google_calendar_event_id);

            $booking->update(['google_calendar_event_id' => null]);

            Log::info("Booking {$booking->id} deleted from Google Calendar");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete booking from Google Calendar: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Sync appointment to Google Calendar
     */
    public function syncAppointmentToCalendar(Appointment $appointment): ?Event
    {
        try {
            if (! $this->client->getAccessToken()) {
                return null;
            }

            $event = new Event([
                'summary' => "Appointment: {$appointment->title}",
                'description' => $appointment->description ?? '',
                'start' => [
                    'dateTime' => $appointment->scheduled_at->toIso8601String(),
                    'timeZone' => config('app.timezone', 'Asia/Jakarta'),
                ],
                'end' => [
                    'dateTime' => $appointment->scheduled_at->copy()->addMinutes($appointment->duration_minutes ?? 60)->toIso8601String(),
                    'timeZone' => config('app.timezone', 'Asia/Jakarta'),
                ],
                'location' => $appointment->location ?? '',
                'attendees' => $this->getAppointmentAttendees($appointment),
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'popup', 'minutes' => 30],
                    ],
                ],
                'extendedProperties' => [
                    'private' => [
                        'appointmentId' => $appointment->id,
                        'tenantId' => $appointment->tenant_id ?? 'unknown',
                        'source' => 'qalcuity-erp',
                    ],
                ],
            ]);

            $calendarId = 'primary';
            $createdEvent = $this->calendarService->events->insert($calendarId, $event);

            $appointment->update([
                'google_calendar_event_id' => $createdEvent->id,
            ]);

            return $createdEvent;
        } catch (\Exception $e) {
            Log::error("Failed to sync appointment to Google Calendar: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Get upcoming events from Google Calendar
     */
    public function getUpcomingEvents(int $days = 7): array
    {
        try {
            if (! $this->client->getAccessToken()) {
                return [];
            }

            $calendarId = 'primary';
            $optParams = [
                'maxResults' => 50,
                'timeMin' => now()->toIso8601String(),
                'timeMax' => now()->addDays($days)->toIso8601String(),
                'orderBy' => 'startTime',
                'singleEvents' => true,
            ];

            $events = $this->calendarService->events->listEvents($calendarId, $optParams);

            return collect($events->getItems())->map(function ($event) {
                return [
                    'id' => $event->id,
                    'summary' => $event->summary,
                    'description' => $event->description,
                    'start' => $event->start->dateTime ?? $event->start->date,
                    'end' => $event->end->dateTime ?? $event->end->date,
                    'location' => $event->location,
                    'status' => $event->status,
                    'is_qalcuity' => isset($event->extendedProperties->private->source) &&
                        $event->extendedProperties->private->source === 'qalcuity-erp',
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to get upcoming events: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Generate booking description for calendar event
     */
    protected function generateBookingDescription(Booking $booking): string
    {
        $roomName = $booking->roomType->name ?? 'N/A';
        $description = "Booking Details:\n";
        $description .= "Guest: {$booking->guest_name}\n";
        $description .= "Room: {$roomName}\n";
        $description .= "Check-in: {$booking->check_in->format('d M Y, H:i')}\n";
        $description .= "Check-out: {$booking->check_out->format('d M Y, H:i')}\n";

        if ($booking->total_amount) {
            $description .= 'Total: Rp '.number_format($booking->total_amount, 0, ',', '.');
        }

        if ($booking->notes) {
            $description .= "\n\nNotes: {$booking->notes}";
        }

        return $description;
    }

    /**
     * Get booking attendees
     */
    protected function getBookingAttendees(Booking $booking): array
    {
        $attendees = [];

        if ($booking->guest_email) {
            $attendees[] = ['email' => $booking->guest_email];
        }

        return $attendees;
    }

    /**
     * Get appointment attendees
     */
    protected function getAppointmentAttendees(Appointment $appointment): array
    {
        $attendees = [];

        if ($appointment->customer_email) {
            $attendees[] = ['email' => $appointment->customer_email];
        }

        return $attendees;
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): array
    {
        $this->client->refreshToken($refreshToken);

        return $this->client->getAccessToken();
    }

    /**
     * Revoke access
     */
    public function revokeAccess(): bool
    {
        try {
            $this->client->revokeToken();

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to revoke Google Calendar access: {$e->getMessage()}");

            return false;
        }
    }
}
