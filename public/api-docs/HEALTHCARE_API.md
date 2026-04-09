# Healthcare API Documentation

## Base URL
```
/api/healthcare
```

## Authentication
All endpoints require Bearer token authentication via `Authorization` header:
```
Authorization: Bearer {your_token}
```

---

## 🧪 Laboratory APIs (4 endpoints)

### 1. GET `/api/healthcare/lab-orders/{id}/results`
Get all lab results for a specific lab order.

**Response:**
```json
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "patient": {...},
      "doctor": {...},
      "exam": {...}
    },
    "results": [...],
    "total_results": 5,
    "verified_count": 3,
    "pending_count": 2,
    "critical_count": 1
  }
}
```

---

### 2. POST `/api/healthcare/lab-results/{id}/approve`
Approve/verify a lab result.

**Request Body:**
```json
{
  "notes": "Results verified and approved"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Lab result approved successfully",
  "data": {
    "id": 1,
    "is_verified": true,
    "verified_by": 1,
    "verified_at": "2026-04-10T10:30:00.000000Z"
  }
}
```

---

### 3. GET `/api/healthcare/lab-equipment/calibration-due`
Get lab equipment due for calibration.

**Query Parameters:**
- `days` (optional): Number of days to look ahead (default: 30)

**Response:**
```json
{
  "success": true,
  "data": {
    "equipment": [...],
    "total_due": 5,
    "overdue": 2,
    "due_within_30_days": 3
  }
}
```

---

### 4. POST `/api/healthcare/lab-samples/{id}/process`
Process a lab sample (update status).

**Request Body:**
```json
{
  "status": "processing",
  "processed_by": 1,
  "notes": "Sample processing started"
}
```

**Valid Statuses:**
- `collected`
- `in_transit`
- `received`
- `processing`
- `completed`
- `rejected`

---

## 🩻 Radiology APIs (3 endpoints)

### 5. GET `/api/healthcare/radiology-exams/{id}/images`
Get images for a radiology exam.

**Response:**
```json
{
  "success": true,
  "data": {
    "exam": {...},
    "images": [...],
    "total_images": 12
  }
}
```

---

### 6. POST `/api/healthcare/radiology-reports/{id}/finalize`
Finalize a radiology report.

**Request Body:**
```json
{
  "findings": "Normal chest X-ray. No abnormalities detected.",
  "impression": "No acute cardiopulmonary disease",
  "recommendations": "Follow up in 6 months"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Radiology report finalized successfully",
  "data": {
    "id": 1,
    "status": "finalized",
    "finalized_at": "2026-04-10T10:30:00.000000Z"
  }
}
```

---

### 7. GET `/api/healthcare/pacs/studies`
Get PACS studies with filters.

**Query Parameters:**
- `patient_id` (optional)
- `modality` (optional): CT, MRI, XRAY, US, etc.
- `status` (optional): pending, in_progress, completed
- `date_from` (optional)
- `date_to` (optional)
- `per_page` (optional, default: 20)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "total": 100,
    "per_page": 20
  }
}
```

---

## 🔪 Surgery APIs (3 endpoints)

### 8. POST `/api/healthcare/surgery-schedules/{id}/assign-team`
Assign surgical team to a surgery schedule.

**Request Body:**
```json
{
  "team_members": [
    {
      "user_id": 1,
      "role": "surgeon"
    },
    {
      "user_id": 2,
      "role": "assistant"
    },
    {
      "user_id": 3,
      "role": "anesthesiologist"
    },
    {
      "user_id": 4,
      "role": "nurse"
    }
  ]
}
```

**Valid Roles:**
- `surgeon`
- `assistant`
- `anesthesiologist`
- `nurse`
- `technician`

---

### 9. GET `/api/healthcare/operating-rooms/availability`
Get operating room availability.

**Query Parameters:**
- `status` (optional): available, occupied, maintenance

**Response:**
```json
{
  "success": true,
  "data": {
    "rooms": [
      {
        "id": 1,
        "room_number": "OR-01",
        "room_name": "Operating Room 1",
        "status": "available",
        "is_available": true,
        "current_surgery": null,
        "next_surgery": {
          "id": 5,
          "patient": "John Doe",
          "scheduled_at": "2026-04-10T14:00:00.000000Z"
        }
      }
    ],
    "total": 5,
    "available": 3,
    "occupied": 1,
    "maintenance": 1
  }
}
```

---

### 10. POST `/api/healthcare/surgery-schedules/{id}/complete`
Complete a surgery schedule.

**Request Body:**
```json
{
  "notes": "Surgery completed successfully",
  "complications": "Minor bleeding, controlled",
  "estimated_blood_loss": 250.5,
  "specimens_collected": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Surgery completed successfully",
  "data": {
    "id": 1,
    "status": "completed",
    "actual_end_time": "2026-04-10T15:30:00.000000Z"
  }
}
```

---

## 💊 Pharmacy APIs (3 endpoints)

### 11. POST `/api/healthcare/prescriptions/{id}/dispense`
Dispense a prescription.

**Request Body:**
```json
{
  "dispensed_by": 1,
  "notes": "All medications dispensed",
  "partial_dispense": false,
  "items_dispensed": [
    {
      "item_id": 1,
      "quantity": 10
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Prescription dispensed successfully",
  "data": {
    "id": 1,
    "status": "dispensed",
    "dispensed_at": "2026-04-10T10:30:00.000000Z"
  }
}
```

---

### 12. GET `/api/healthcare/medications/expiring`
Get medications expiring soon.

**Query Parameters:**
- `days` (optional): Number of days to look ahead (default: 90)

**Response:**
```json
{
  "success": true,
  "data": {
    "medications": [...],
    "inventory": [...],
    "total_expiring": 15,
    "expired": 3,
    "expiring_within_30_days": 5,
    "expiring_within_90_days": 7
  }
}
```

---

### 13. POST `/api/healthcare/pharmacy/stock-opname`
Create pharmacy stock opname (stock count).

**Request Body:**
```json
{
  "items": [
    {
      "medicine_id": 1,
      "warehouse_id": 1,
      "physical_quantity": 100,
      "notes": "Stock count completed"
    }
  ],
  "opname_date": "2026-04-10",
  "performed_by": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Pharmacy stock opname completed successfully",
  "data": null
}
```

---

## 🏥 Inpatient APIs (3 endpoints)

### 14. POST `/api/healthcare/admissions/{id}/transfer-ward`
Transfer patient to different ward/bed.

**Request Body:**
```json
{
  "new_ward_id": 2,
  "new_bed_id": 5,
  "transfer_reason": "Patient requires specialized care",
  "notes": "Transferred from general ward to ICU"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Patient transferred successfully",
  "data": {
    "id": 1,
    "ward_id": 2,
    "bed_id": 5,
    "status": "admitted"
  }
}
```

---

### 15. GET `/api/healthcare/beds/availability`
Get bed availability with detailed info.

**Query Parameters:**
- `ward_id` (optional)
- `bed_type` (optional): general, icu, vip, etc.
- `status` (optional): available, occupied, maintenance
- `gender` (optional): male, female

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total": 100,
      "available": 45,
      "occupied": 50,
      "maintenance": 5,
      "occupancy_rate": 50.0
    },
    "by_ward": {
      "1": {
        "total": 30,
        "available": 15,
        "occupied": 13,
        "maintenance": 2,
        "beds": [...]
      }
    },
    "beds": [...]
  }
}
```

---

### 16. POST `/api/healthcare/admissions/{id}/discharge`
Discharge a patient (enhanced version).

**Request Body:**
```json
{
  "discharge_diagnosis": "Acute appendicitis - resolved",
  "discharge_notes": "Patient recovered well, no complications",
  "discharge_type": "regular",
  "discharge_condition": "improved",
  "follow_up_required": true,
  "follow_up_date": "2026-04-17",
  "medications_on_discharge": "Amoxicillin 500mg 3x daily for 7 days",
  "restrictions": "No heavy lifting for 2 weeks"
}
```

**Valid Discharge Types:**
- `regular`
- `ama` (Against Medical Advice)
- `referral`
- `transfer`
- `deceased`

**Valid Discharge Conditions:**
- `improved`
- `stable`
- `worse`
- `deceased`

**Response:**
```json
{
  "success": true,
  "message": "Patient discharged successfully",
  "data": {
    "id": 1,
    "status": "discharged",
    "actual_discharge_date": "2026-04-10T16:00:00.000000Z",
    "discharge_type": "regular",
    "discharge_status": "improved"
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "This action is unauthorized"
}
```

---

## Rate Limiting

- **Read endpoints**: 60 requests/minute (base, scaled by plan)
- **Write endpoints**: 20 requests/minute (base, scaled by plan)

Headers included in response:
- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `Retry-After` (when limit exceeded)

---

## Notes

1. All timestamps are in ISO 8601 format (UTC)
2. All monetary values are in decimal format (2 decimal places)
3. Pagination uses Laravel's default paginator
4. Tenant isolation is enforced automatically via middleware
5. All write operations are wrapped in database transactions
6. Automatic notifications are triggered for critical events
