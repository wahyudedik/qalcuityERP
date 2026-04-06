# 🐟 Fisheries Module - Quick Start Guide

## 🚀 Getting Started in 3 Steps

### Step 1: Enable the Module
Ensure fisheries module is enabled for your tenant in tenant settings.

### Step 2: Access the Dashboard
Navigate to: **Sidebar → 🐟 Dashboard Perikanan**

Or visit: `/fisheries`

### Step 3: Start Using
Click any feature card to begin:
- ❄️ Cold Chain Management
- ⚓ Fishing Operations  
- 🐠 Aquaculture
- 📋 Species & Grading
- 📦 Export Documentation
- 📊 Analytics

---

## 📍 Navigation Map

```
Fisheries Module
├── 🐟 Dashboard (Overview)
│   └── /fisheries
│
├── ❄️ Cold Chain
│   ├── List View: /fisheries/cold-chain
│   └── Detail: /fisheries/cold-chain/{id}
│
├── ⚓ Fishing Operations
│   ├── List View: /fisheries/operations
│   └── Detail: /fisheries/operations/{id}
│
├── 🐠 Aquaculture
│   ├── List View: /fisheries/aquaculture
│   └── Detail: /fisheries/aquaculture/{id}
│
├── 📋 Species & Grading
│   └── /fisheries/species
│
├── 📦 Export Documentation
│   └── /fisheries/export
│
└── 📊 Analytics
    └── /fisheries/analytics
```

---

## 🎯 Common Tasks

### Track Temperature
1. Go to **Cold Chain**
2. Click storage unit
3. Click **"🌡️ Log Temperature"**
4. Enter reading and save

### Record Fishing Catch
1. Go to **Fishing Operations**
2. Click active trip
3. Click **"➕ Catat Tangkapan"**
4. Fill species, weight, grade
5. Save

### Monitor Pond Health
1. Go to **Aquaculture**
2. Click pond
3. Check water quality dashboard
4. Click **"💧 Log Kualitas Air"** to update

### View Performance
1. Go to **Analytics**
2. Select time period
3. Review metrics and charts
4. Click **"🖨️ Print Report"**

---

## 💡 Pro Tips

### Cold Chain
- Set up alerts for automatic notifications
- Monitor utilization to optimize storage
- Check compliance reports monthly

### Fishing Operations
- Record catches immediately after landing
- Use GPS coordinates for tracking zones
- Grade fish consistently for accurate pricing

### Aquaculture
- Test water quality daily
- Track FCR (Feed Conversion Ratio)
- Maintain optimal pH 6.5-8.5

### Analytics
- Review weekly trends every Monday
- Compare month-over-month performance
- Export reports for stakeholder meetings

---

## 🔑 Key Shortcuts

| Action | Method |
|--------|--------|
| New Cold Storage | Click "➕ Tambah Unit" |
| Plan Fishing Trip | Click "🚢 Buat Trip Baru" |
| Add Pond | Click "🏊 Tambah Kolam" |
| Add Species | Click "➕ Tambah Spesies" |
| Apply for Permit | Click "📝 Ajukan Izin Baru" |
| Log Temperature | Click "🌡️ Update Suhu" |
| Record Catch | Click "🐟 Catat Tangkapan" |
| Check Water Quality | Click "💧 Cek Kualitas Air" |
| View Analytics | Navigate to /fisheries/analytics |

---

## 📊 What to Monitor Daily

### Morning Checklist
- [ ] Check cold storage temperatures
- [ ] Review active fishing trips
- [ ] Test pond water quality
- [ ] Check for temperature alerts

### Weekly Review
- [ ] Analyze catch trends
- [ ] Review revenue performance
- [ ] Check feed inventory
- [ ] Update export documentation

### Monthly Analysis
- [ ] Run analytics report
- [ ] Calculate efficiency metrics
- [ ] Review species performance
- [ ] Assess cold chain compliance

---

## 🆘 Troubleshooting

**Issue**: Can't see fisheries menu
- **Solution**: Check if module enabled in tenant settings

**Issue**: No data showing
- **Solution**: Add sample data first (species, ponds, vessels)

**Issue**: Charts not displaying
- **Solution**: Ensure you have data for selected time period

**Issue**: Forms not submitting
- **Solution**: Check all required fields filled, verify CSRF token

---

## 📞 Need Help?

1. Check full documentation: `/docs/FISHERIES_*`
2. Review controller: `app/Http/Controllers/Fisheries/FisheriesViewController.php`
3. Inspect browser console for errors
4. Check Laravel logs: `storage/logs/laravel.log`

---

*Quick Reference v1.0 | Last Updated: April 6, 2026*
