{{--
    NAV_GROUPS untuk Modul Industri Spesifik
    Hanya di-render jika modul diaktifkan oleh tenant.
    Di-include di dalam blok <script> NAV_GROUPS di app.blade.php.
--}}
@if (!$user?->isKasir() && !$user?->isGudang() && !$user?->isSuperAdmin() && !$user?->isAffiliate())

    @if ($navTenant?->isModuleEnabled('hotel') ?? false)
        hotel: {
            title: 'Hotel & PMS',
            items: [
                { section: 'Hotel PMS' },
                {
                    label: 'Dashboard Hotel',
                    href: '{{ route("hotel.dashboard") }}',
                    active: {{ request()->routeIs('hotel.dashboard') ? 'true' : 'false' }}
                }, {
                    label: 'Tipe Kamar',
                    href: '{{ route("hotel.room-types.index") }}',
                    active: {{ request()->routeIs('hotel.room-types*') ? 'true' : 'false' }}
                }, {
                    label: 'Kamar',
                    href: '{{ route("hotel.rooms.index") }}',
                    active: {{ request()->routeIs('hotel.rooms.index', 'hotel.rooms.show', 'hotel.rooms.edit') ? 'true' : 'false' }}
                }, {
                    label: 'Ketersediaan Kamar',
                    href: '{{ route("hotel.rooms.availability") }}',
                    active: {{ request()->routeIs('hotel.rooms.availability*') ? 'true' : 'false' }}
                }, {
                    label: 'Reservasi',
                    href: '{{ route("hotel.reservations.index") }}',
                    active: {{ request()->routeIs('hotel.reservations*') ? 'true' : 'false' }}
                }, {
                    label: 'Tamu',
                    href: '{{ route("hotel.guests.index") }}',
                    active: {{ request()->routeIs('hotel.guests*') ? 'true' : 'false' }}
                }, {
                    label: 'Check-in / Check-out',
                    href: '{{ route("hotel.checkin-out.index") }}',
                    active: {{ request()->routeIs('hotel.checkin-out*', 'hotel.checkin*', 'hotel.checkout*') ? 'true' : 'false' }}
                }, {
                    label: 'Housekeeping',
                    href: '{{ route("hotel.housekeeping.index") }}',
                    active: {{ request()->routeIs('hotel.housekeeping*') ? 'true' : 'false' }}
                }, {
                    label: 'Tarif Kamar',
                    href: '{{ route("hotel.rates.index") }}',
                    active: {{ request()->routeIs('hotel.rates*') ? 'true' : 'false' }}
                }, {
                    label: 'Channel Distribution',
                    href: '{{ route("hotel.channels.index") }}',
                    active: {{ request()->routeIs('hotel.channels*') ? 'true' : 'false' }}
                }, {
                    label: 'Pengaturan Hotel',
                    href: '{{ route("hotel.settings.edit") }}',
                    active: {{ request()->routeIs('hotel.settings*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('telecom') ?? false)
        telecom: {
            title: 'Telecom / ISP',
            items: [
                { section: 'Overview' },
                {
                    label: 'Dashboard Telecom',
                    href: '{{ route("telecom.dashboard") }}',
                    active: {{ request()->routeIs('telecom.dashboard') ? 'true' : 'false' }}
                },
                { section: 'Pelanggan & Paket' },
                {
                    label: 'Langganan',
                    href: '{{ route("telecom.subscriptions.index") }}',
                    active: {{ request()->routeIs('telecom.subscriptions*') ? 'true' : 'false' }}
                }, {
                    label: 'Paket Internet',
                    href: '{{ route("telecom.packages.index") }}',
                    active: {{ request()->routeIs('telecom.packages*') ? 'true' : 'false' }}
                },
                { section: 'Jaringan' },
                {
                    label: 'Perangkat Jaringan',
                    href: '{{ route("telecom.devices.index") }}',
                    active: {{ request()->routeIs('telecom.devices*') ? 'true' : 'false' }}
                }, {
                    label: 'Voucher Hotspot',
                    href: '{{ route("telecom.vouchers.index") }}',
                    active: {{ request()->routeIs('telecom.vouchers*') ? 'true' : 'false' }}
                }, {
                    label: 'Peta Jaringan',
                    href: '{{ route("telecom.maps") }}',
                    active: {{ request()->routeIs('telecom.maps*') ? 'true' : 'false' }}
                },
                { section: 'Laporan' },
                {
                    label: 'Laporan Telecom',
                    href: '{{ route("telecom.reports.index") }}',
                    active: {{ request()->routeIs('telecom.reports*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('agriculture') ?? false)
        agriculture: {
            title: 'Pertanian',
            items: [
                { section: 'Lahan & Tanam' },
                {
                    label: 'Manajemen Lahan',
                    href: '{{ route("farm.plots") }}',
                    active: {{ request()->routeIs('farm.plots*') ? 'true' : 'false' }}
                }, {
                    label: 'Siklus Tanam',
                    href: '{{ route("farm.cycles") }}',
                    active: {{ request()->routeIs('farm.cycles*') ? 'true' : 'false' }}
                }, {
                    label: 'Pencatatan Panen',
                    href: '{{ route("farm.harvests") }}',
                    active: {{ request()->routeIs('farm.harvests*') ? 'true' : 'false' }}
                }, {
                    label: 'Analisis Biaya Lahan',
                    href: '{{ route("farm.analytics") }}',
                    active: {{ request()->routeIs('farm.analytics*') ? 'true' : 'false' }}
                }, {
                    label: 'Populasi Ternak',
                    href: '{{ route("farm.livestock") }}',
                    active: {{ request()->routeIs('farm.livestock*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('livestock') ?? false)
        livestock: {
            title: 'Peternakan & Perikanan',
            items: [
                { section: 'Peternakan' },
                {
                    label: 'Dairy Management',
                    href: '{{ route("livestock-enhancement.dairy.milk-records") }}',
                    active: {{ request()->routeIs('livestock-enhancement.dairy*') ? 'true' : 'false' }}
                }, {
                    label: 'Poultry Management',
                    href: '{{ route("livestock-enhancement.poultry.flocks") }}',
                    active: {{ request()->routeIs('livestock-enhancement.poultry*') ? 'true' : 'false' }}
                }, {
                    label: 'Breeding',
                    href: '{{ route("livestock-enhancement.breeding.records") }}',
                    active: {{ request()->routeIs('livestock-enhancement.breeding*') ? 'true' : 'false' }}
                }, {
                    label: 'Health & Vaccination',
                    href: '{{ route("livestock-enhancement.health.treatments") }}',
                    active: {{ request()->routeIs('livestock-enhancement.health*') ? 'true' : 'false' }}
                },
                { section: 'Perikanan' },
                {
                    label: 'Dashboard Perikanan',
                    href: '{{ route("fisheries.index") }}',
                    active: {{ request()->routeIs('fisheries.index') ? 'true' : 'false' }}
                }, {
                    label: 'Aquaculture',
                    href: '{{ route("fisheries.aquaculture.index") }}',
                    active: {{ request()->routeIs('fisheries.aquaculture*') ? 'true' : 'false' }}
                }, {
                    label: 'Cold Chain',
                    href: '{{ route("fisheries.cold-chain.index") }}',
                    active: {{ request()->routeIs('fisheries.cold-chain*') ? 'true' : 'false' }}
                }, {
                    label: 'Analytics',
                    href: '{{ route("fisheries.analytics") }}',
                    active: {{ request()->routeIs('fisheries.analytics') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('fnb') ?? false)
        fnb: {
            title: 'F&B / Restoran',
            items: [
                { section: 'Operasional' },
                {
                    label: 'Manajemen Meja',
                    href: '{{ route("fnb.tables.index") }}',
                    active: {{ request()->routeIs('fnb.tables*') ? 'true' : 'false' }}
                }, {
                    label: 'Kitchen Display (KDS)',
                    href: '{{ route("fnb.kds.index") }}',
                    active: {{ request()->routeIs('fnb.kds*') ? 'true' : 'false' }}
                },
                { section: 'Menu & Resep' },
                {
                    label: 'Resep & HPP',
                    href: '{{ route("fnb.recipes.index") }}',
                    active: {{ request()->routeIs('fnb.recipes*') ? 'true' : 'false' }}
                }, {
                    label: 'Waste Tracking',
                    href: '{{ route("fnb.waste.index") }}',
                    active: {{ request()->routeIs('fnb.waste*') ? 'true' : 'false' }}
                },
                { section: 'Kasir' },
                {
                    label: 'Kasir (POS)',
                    href: '{{ route("pos.index") }}',
                    active: {{ request()->routeIs('pos*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('spa') ?? false)
        spa: {
            title: 'Spa & Wellness',
            items: [
                { section: 'Spa' },
                {
                    label: 'Dashboard Spa',
                    href: '{{ route("hotel.spa.dashboard") }}',
                    active: {{ request()->routeIs('hotel.spa.dashboard') ? 'true' : 'false' }}
                }, {
                    label: 'Booking Spa',
                    href: '{{ route("hotel.spa.bookings.index") }}',
                    active: {{ request()->routeIs('hotel.spa.bookings*') ? 'true' : 'false' }}
                }, {
                    label: 'Terapis',
                    href: '{{ route("hotel.spa.therapists.index") }}',
                    active: {{ request()->routeIs('hotel.spa.therapists*') ? 'true' : 'false' }}
                }, {
                    label: 'Treatment & Paket',
                    href: '{{ route("hotel.spa.treatments.index") }}',
                    active: {{ request()->routeIs('hotel.spa.treatments*') ? 'true' : 'false' }}
                }, {
                    label: 'Paket Spa',
                    href: '{{ route("hotel.spa.packages.index") }}',
                    active: {{ request()->routeIs('hotel.spa.packages*') ? 'true' : 'false' }}
                }, {
                    label: 'Laporan Spa',
                    href: '{{ route("hotel.spa.reports.index") }}',
                    active: {{ request()->routeIs('hotel.spa.reports*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif
    @if ($navTenant?->isModuleEnabled('healthcare') ?? false)
        healthcare: {
            title: 'SimRS / Healthcare',
            items: [
                { section: 'Overview' },
                {
                    label: 'Dashboard SimRS',
                    href: '{{ route("healthcare.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.dashboard') ? 'true' : 'false' }}
                },
                { section: 'Pasien & Pendaftaran' },
                {
                    label: 'Data Pasien',
                    href: '{{ route("healthcare.patients.index") }}',
                    active: {{ request()->routeIs('healthcare.patients*') ? 'true' : 'false' }}
                }, {
                    label: 'Antrian',
                    href: '{{ route("healthcare.queue.index") }}',
                    active: {{ request()->routeIs('healthcare.queue*') ? 'true' : 'false' }}
                }, {
                    label: 'Jadwal Dokter',
                    href: '{{ route("healthcare.appointments.index") }}',
                    active: {{ request()->routeIs('healthcare.appointments*') ? 'true' : 'false' }}
                }, {
                    label: 'Dokter',
                    href: '{{ route("healthcare.doctors.index") }}',
                    active: {{ request()->routeIs('healthcare.doctors*') ? 'true' : 'false' }}
                },
                { section: 'Rawat Inap' },
                {
                    label: 'Dashboard Rawat Inap',
                    href: '{{ route("healthcare.inpatient.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.inpatient*') ? 'true' : 'false' }}
                }, {
                    label: 'Manajemen Tempat Tidur',
                    href: '{{ route("healthcare.beds.index") }}',
                    active: {{ request()->routeIs('healthcare.beds*') ? 'true' : 'false' }}
                }, {
                    label: 'Bangsal / Ward',
                    href: '{{ route("healthcare.wards.index") }}',
                    active: {{ request()->routeIs('healthcare.wards*') ? 'true' : 'false' }}
                },
                { section: 'IGD & Operasi' },
                {
                    label: 'IGD / Emergency',
                    href: '{{ route("healthcare.er.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.er*') ? 'true' : 'false' }}
                }, {
                    label: 'Triage',
                    href: '{{ route("healthcare.triage.index") }}',
                    active: {{ request()->routeIs('healthcare.triage*') ? 'true' : 'false' }}
                }, {
                    label: 'Jadwal Operasi',
                    href: '{{ route("healthcare.surgery-schedules.index") }}',
                    active: {{ request()->routeIs('healthcare.surgery-schedules*') ? 'true' : 'false' }}
                },
                { section: 'Rekam Medis & Klinis' },
                {
                    label: 'Rekam Medis (EMR)',
                    href: '{{ route("healthcare.emr.index") }}',
                    active: {{ request()->routeIs('healthcare.emr*') ? 'true' : 'false' }}
                }, {
                    label: 'Laboratorium',
                    href: '{{ route("healthcare.laboratory.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.laboratory*', 'healthcare.lab-results*') ? 'true' : 'false' }}
                }, {
                    label: 'Radiologi',
                    href: '{{ route("healthcare.radiology.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.radiology*') ? 'true' : 'false' }}
                }, {
                    label: 'Farmasi',
                    href: '{{ route("healthcare.pharmacy.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.pharmacy*') ? 'true' : 'false' }}
                },
                { section: 'Keuangan & BPJS' },
                {
                    label: 'Billing & Tagihan',
                    href: '{{ route("healthcare.billing.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.billing*') ? 'true' : 'false' }}
                }, {
                    label: 'Klaim BPJS',
                    href: '{{ route("healthcare.bpjs-claims.index") }}',
                    active: {{ request()->routeIs('healthcare.bpjs-claims*') ? 'true' : 'false' }}
                }, {
                    label: 'Asuransi',
                    href: '{{ route("healthcare.insurance-claims.index") }}',
                    active: {{ request()->routeIs('healthcare.insurance-claims*') ? 'true' : 'false' }}
                },
                { section: 'Telemedicine' },
                {
                    label: 'Telemedicine',
                    href: '{{ route("healthcare.telemedicine.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.telemedicine*') ? 'true' : 'false' }}
                },
                { section: 'Laporan & Analitik' },
                {
                    label: 'Analitik RS',
                    href: '{{ route("healthcare.analytics.dashboard") }}',
                    active: {{ request()->routeIs('healthcare.analytics*') ? 'true' : 'false' }}
                }, {
                    label: 'Laporan Kemenkes',
                    href: '{{ route("healthcare.ministry-reports.index") }}',
                    active: {{ request()->routeIs('healthcare.ministry-reports*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif
    @if ($navTenant?->isModuleEnabled('tour_travel') ?? false)
        tour_travel: {
            title: 'Tour & Travel',
            items: [
                { section: 'Paket & Booking' },
                {
                    label: 'Paket Wisata',
                    href: '{{ route("tour-travel.packages.index") }}',
                    active: {{ request()->routeIs('tour-travel.packages*') ? 'true' : 'false' }}
                }, {
                    label: 'Booking',
                    href: '{{ route("tour-travel.bookings.index") }}',
                    active: {{ request()->routeIs('tour-travel.bookings*') ? 'true' : 'false' }}
                }, {
                    label: 'Analitik Travel',
                    href: '{{ route("tour-travel.analytics") }}',
                    active: {{ request()->routeIs('tour-travel.analytics*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('construction') ?? false)
        construction: {
            title: 'Konstruksi',
            items: [
                { section: 'Proyek' },
                {
                    label: 'Subkontraktor',
                    href: '{{ route("construction.subcontractors.index") }}',
                    active: {{ request()->routeIs('construction.subcontractors*') ? 'true' : 'false' }}
                }, {
                    label: 'Pengiriman Material',
                    href: '{{ route("construction.deliveries.index") }}',
                    active: {{ request()->routeIs('construction.deliveries*') ? 'true' : 'false' }}
                }, {
                    label: 'Laporan Harian',
                    href: '{{ route("construction.reports.index") }}',
                    active: {{ request()->routeIs('construction.reports*') ? 'true' : 'false' }}
                }, {
                    label: 'Gantt Chart',
                    href: '{{ route("projects.index") }}',
                    active: {{ request()->routeIs('construction.gantt*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('cosmetic') ?? false)
        cosmetic: {
            title: 'Kosmetik & Beauty',
            items: [
                { section: 'Produksi' },
                {
                    label: 'Formula',
                    href: '{{ route("cosmetic.formulas.index") }}',
                    active: {{ request()->routeIs('cosmetic.formulas*') ? 'true' : 'false' }}
                }, {
                    label: 'Batch Produksi',
                    href: '{{ route("cosmetic.batches.index") }}',
                    active: {{ request()->routeIs('cosmetic.batches*') ? 'true' : 'false' }}
                }, {
                    label: 'QC Lab',
                    href: '{{ route("cosmetic.qc.tests") }}',
                    active: {{ request()->routeIs('cosmetic.qc*') ? 'true' : 'false' }}
                },
                { section: 'Regulasi' },
                {
                    label: 'BPOM Dashboard',
                    href: '{{ route("cosmetic.bpom.dashboard") }}',
                    active: {{ request()->routeIs('cosmetic.bpom*') ? 'true' : 'false' }}
                }, {
                    label: 'Registrasi',
                    href: '{{ route("cosmetic.registrations.index") }}',
                    active: {{ request()->routeIs('cosmetic.registrations*') ? 'true' : 'false' }}
                }, {
                    label: 'Analitik',
                    href: '{{ route("cosmetic.analytics.dashboard") }}',
                    active: {{ request()->routeIs('cosmetic.analytics*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif

    @if ($navTenant?->isModuleEnabled('printing') ?? false)
        printing: {
            title: 'Percetakan',
            items: [
                { section: 'Job Order' },
                {
                    label: 'Dashboard Cetak',
                    href: '{{ route("printing.dashboard") }}',
                    active: {{ request()->routeIs('printing.dashboard') ? 'true' : 'false' }}
                }, {
                    label: 'Estimasi Cetak',
                    href: '{{ route("printing.estimates") }}',
                    active: {{ request()->routeIs('printing.estimates*') ? 'true' : 'false' }}
                },
            ]
        },
    @endif
@endif