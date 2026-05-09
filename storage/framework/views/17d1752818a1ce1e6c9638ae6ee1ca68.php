
<?php if(!$user?->isKasir() && !$user?->isGudang() && !$user?->isSuperAdmin() && !$user?->isAffiliate()): ?>

    <?php if($navTenant?->isModuleEnabled('hotel') ?? false): ?>
        hotel: {
            title: 'Hotel & PMS',
            items: [
                { section: 'Hotel PMS' },
                {
                    label: 'Dashboard Hotel',
                    href: '<?php echo e(route("hotel.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.dashboard') ? 'true' : 'false'); ?>

                }, {
                    label: 'Tipe Kamar',
                    href: '<?php echo e(route("hotel.room-types.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.room-types*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Kamar',
                    href: '<?php echo e(route("hotel.rooms.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.rooms.index', 'hotel.rooms.show', 'hotel.rooms.edit') ? 'true' : 'false'); ?>

                }, {
                    label: 'Ketersediaan Kamar',
                    href: '<?php echo e(route("hotel.rooms.availability")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.rooms.availability*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Reservasi',
                    href: '<?php echo e(route("hotel.reservations.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.reservations*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Tamu',
                    href: '<?php echo e(route("hotel.guests.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.guests*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Check-in / Check-out',
                    href: '<?php echo e(route("hotel.checkin-out.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.checkin-out*', 'hotel.checkin*', 'hotel.checkout*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Housekeeping',
                    href: '<?php echo e(route("hotel.housekeeping.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.housekeeping*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Tarif Kamar',
                    href: '<?php echo e(route("hotel.rates.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.rates*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Channel Distribution',
                    href: '<?php echo e(route("hotel.channels.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.channels*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Pengaturan Hotel',
                    href: '<?php echo e(route("hotel.settings.edit")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.settings*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('telecom') ?? false): ?>
        telecom: {
            title: 'Telecom / ISP',
            items: [
                { section: 'Overview' },
                {
                    label: 'Dashboard Telecom',
                    href: '<?php echo e(route("telecom.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('telecom.dashboard') ? 'true' : 'false'); ?>

                },
                { section: 'Pelanggan & Paket' },
                {
                    label: 'Langganan',
                    href: '<?php echo e(route("telecom.subscriptions.index")); ?>',
                    active: <?php echo e(request()->routeIs('telecom.subscriptions*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Paket Internet',
                    href: '<?php echo e(route("telecom.packages.index")); ?>',
                    active: <?php echo e(request()->routeIs('telecom.packages*') ? 'true' : 'false'); ?>

                },
                { section: 'Jaringan' },
                {
                    label: 'Perangkat Jaringan',
                    href: '<?php echo e(route("telecom.devices.index")); ?>',
                    active: <?php echo e(request()->routeIs('telecom.devices*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Voucher Hotspot',
                    href: '<?php echo e(route("telecom.vouchers.index")); ?>',
                    active: <?php echo e(request()->routeIs('telecom.vouchers*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Peta Jaringan',
                    href: '<?php echo e(route("telecom.maps")); ?>',
                    active: <?php echo e(request()->routeIs('telecom.maps*') ? 'true' : 'false'); ?>

                },
                { section: 'Laporan' },
                {
                    label: 'Laporan Telecom',
                    href: '<?php echo e(route("telecom.reports.index")); ?>',
                    active: <?php echo e(request()->routeIs('telecom.reports*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('agriculture') ?? false): ?>
        agriculture: {
            title: 'Pertanian',
            items: [
                { section: 'Lahan & Tanam' },
                {
                    label: 'Manajemen Lahan',
                    href: '<?php echo e(route("farm.plots")); ?>',
                    active: <?php echo e(request()->routeIs('farm.plots*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Siklus Tanam',
                    href: '<?php echo e(route("farm.cycles")); ?>',
                    active: <?php echo e(request()->routeIs('farm.cycles*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Pencatatan Panen',
                    href: '<?php echo e(route("farm.harvests")); ?>',
                    active: <?php echo e(request()->routeIs('farm.harvests*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Analisis Biaya Lahan',
                    href: '<?php echo e(route("farm.analytics")); ?>',
                    active: <?php echo e(request()->routeIs('farm.analytics*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Populasi Ternak',
                    href: '<?php echo e(route("farm.livestock")); ?>',
                    active: <?php echo e(request()->routeIs('farm.livestock*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('livestock') ?? false): ?>
        livestock: {
            title: 'Peternakan & Perikanan',
            items: [
                { section: 'Peternakan' },
                {
                    label: 'Dairy Management',
                    href: '<?php echo e(route("livestock-enhancement.dairy.milk-records")); ?>',
                    active: <?php echo e(request()->routeIs('livestock-enhancement.dairy*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Poultry Management',
                    href: '<?php echo e(route("livestock-enhancement.poultry.flocks")); ?>',
                    active: <?php echo e(request()->routeIs('livestock-enhancement.poultry*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Breeding',
                    href: '<?php echo e(route("livestock-enhancement.breeding.records")); ?>',
                    active: <?php echo e(request()->routeIs('livestock-enhancement.breeding*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Health & Vaccination',
                    href: '<?php echo e(route("livestock-enhancement.health.treatments")); ?>',
                    active: <?php echo e(request()->routeIs('livestock-enhancement.health*') ? 'true' : 'false'); ?>

                },
                { section: 'Perikanan' },
                {
                    label: 'Dashboard Perikanan',
                    href: '<?php echo e(route("fisheries.index")); ?>',
                    active: <?php echo e(request()->routeIs('fisheries.index') ? 'true' : 'false'); ?>

                }, {
                    label: 'Aquaculture',
                    href: '<?php echo e(route("fisheries.aquaculture.index")); ?>',
                    active: <?php echo e(request()->routeIs('fisheries.aquaculture*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Cold Chain',
                    href: '<?php echo e(route("fisheries.cold-chain.index")); ?>',
                    active: <?php echo e(request()->routeIs('fisheries.cold-chain*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Analytics',
                    href: '<?php echo e(route("fisheries.analytics")); ?>',
                    active: <?php echo e(request()->routeIs('fisheries.analytics') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('fnb') ?? false): ?>
        fnb: {
            title: 'F&B / Restoran',
            items: [
                { section: 'Operasional' },
                {
                    label: 'Manajemen Meja',
                    href: '<?php echo e(route("fnb.tables.index")); ?>',
                    active: <?php echo e(request()->routeIs('fnb.tables*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Kitchen Display (KDS)',
                    href: '<?php echo e(route("fnb.kds.index")); ?>',
                    active: <?php echo e(request()->routeIs('fnb.kds*') ? 'true' : 'false'); ?>

                },
                { section: 'Menu & Resep' },
                {
                    label: 'Resep & HPP',
                    href: '<?php echo e(route("fnb.recipes.index")); ?>',
                    active: <?php echo e(request()->routeIs('fnb.recipes*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Waste Tracking',
                    href: '<?php echo e(route("fnb.waste.index")); ?>',
                    active: <?php echo e(request()->routeIs('fnb.waste*') ? 'true' : 'false'); ?>

                },
                { section: 'Kasir' },
                {
                    label: 'Kasir (POS)',
                    href: '<?php echo e(route("pos.index")); ?>',
                    active: <?php echo e(request()->routeIs('pos*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('spa') ?? false): ?>
        spa: {
            title: 'Spa & Wellness',
            items: [
                { section: 'Spa' },
                {
                    label: 'Dashboard Spa',
                    href: '<?php echo e(route("hotel.spa.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.spa.dashboard') ? 'true' : 'false'); ?>

                }, {
                    label: 'Booking Spa',
                    href: '<?php echo e(route("hotel.spa.bookings.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.spa.bookings*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Terapis',
                    href: '<?php echo e(route("hotel.spa.therapists.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.spa.therapists*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Treatment & Paket',
                    href: '<?php echo e(route("hotel.spa.treatments.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.spa.treatments*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Paket Spa',
                    href: '<?php echo e(route("hotel.spa.packages.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.spa.packages*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Laporan Spa',
                    href: '<?php echo e(route("hotel.spa.reports.index")); ?>',
                    active: <?php echo e(request()->routeIs('hotel.spa.reports*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>
    <?php if($navTenant?->isModuleEnabled('healthcare') ?? false): ?>
        healthcare: {
            title: 'SimRS / Healthcare',
            items: [
                { section: 'Overview' },
                {
                    label: 'Dashboard SimRS',
                    href: '<?php echo e(route("healthcare.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.dashboard') ? 'true' : 'false'); ?>

                },
                { section: 'Pasien & Pendaftaran' },
                {
                    label: 'Data Pasien',
                    href: '<?php echo e(route("healthcare.patients.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.patients*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Antrian',
                    href: '<?php echo e(route("healthcare.queue.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.queue*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Jadwal Dokter',
                    href: '<?php echo e(route("healthcare.appointments.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.appointments*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Dokter',
                    href: '<?php echo e(route("healthcare.doctors.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.doctors*') ? 'true' : 'false'); ?>

                },
                { section: 'Rawat Inap' },
                {
                    label: 'Dashboard Rawat Inap',
                    href: '<?php echo e(route("healthcare.inpatient.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.inpatient*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Manajemen Tempat Tidur',
                    href: '<?php echo e(route("healthcare.beds.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.beds*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Bangsal / Ward',
                    href: '<?php echo e(route("healthcare.wards.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.wards*') ? 'true' : 'false'); ?>

                },
                { section: 'IGD & Operasi' },
                {
                    label: 'IGD / Emergency',
                    href: '<?php echo e(route("healthcare.er.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.er*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Triage',
                    href: '<?php echo e(route("healthcare.triage.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.triage*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Jadwal Operasi',
                    href: '<?php echo e(route("healthcare.surgery-schedules.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.surgery-schedules*') ? 'true' : 'false'); ?>

                },
                { section: 'Rekam Medis & Klinis' },
                {
                    label: 'Rekam Medis (EMR)',
                    href: '<?php echo e(route("healthcare.emr.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.emr*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Laboratorium',
                    href: '<?php echo e(route("healthcare.laboratory.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.laboratory*', 'healthcare.lab-results*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Radiologi',
                    href: '<?php echo e(route("healthcare.radiology.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.radiology*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Farmasi',
                    href: '<?php echo e(route("healthcare.pharmacy.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.pharmacy*') ? 'true' : 'false'); ?>

                },
                { section: 'Keuangan & BPJS' },
                {
                    label: 'Billing & Tagihan',
                    href: '<?php echo e(route("healthcare.billing.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.billing*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Klaim BPJS',
                    href: '<?php echo e(route("healthcare.bpjs-claims.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.bpjs-claims*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Asuransi',
                    href: '<?php echo e(route("healthcare.insurance-claims.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.insurance-claims*') ? 'true' : 'false'); ?>

                },
                { section: 'Telemedicine' },
                {
                    label: 'Telemedicine',
                    href: '<?php echo e(route("healthcare.telemedicine.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.telemedicine*') ? 'true' : 'false'); ?>

                },
                { section: 'Laporan & Analitik' },
                {
                    label: 'Analitik RS',
                    href: '<?php echo e(route("healthcare.analytics.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.analytics*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Laporan Kemenkes',
                    href: '<?php echo e(route("healthcare.ministry-reports.index")); ?>',
                    active: <?php echo e(request()->routeIs('healthcare.ministry-reports*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>
    <?php if($navTenant?->isModuleEnabled('tour_travel') ?? false): ?>
        tour_travel: {
            title: 'Tour & Travel',
            items: [
                { section: 'Paket & Booking' },
                {
                    label: 'Paket Wisata',
                    href: '<?php echo e(route("tour-travel.packages.index")); ?>',
                    active: <?php echo e(request()->routeIs('tour-travel.packages*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Booking',
                    href: '<?php echo e(route("tour-travel.bookings.index")); ?>',
                    active: <?php echo e(request()->routeIs('tour-travel.bookings*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Analitik Travel',
                    href: '<?php echo e(route("tour-travel.analytics")); ?>',
                    active: <?php echo e(request()->routeIs('tour-travel.analytics*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('construction') ?? false): ?>
        construction: {
            title: 'Konstruksi',
            items: [
                { section: 'Proyek' },
                {
                    label: 'Subkontraktor',
                    href: '<?php echo e(route("construction.subcontractors.index")); ?>',
                    active: <?php echo e(request()->routeIs('construction.subcontractors*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Pengiriman Material',
                    href: '<?php echo e(route("construction.deliveries.index")); ?>',
                    active: <?php echo e(request()->routeIs('construction.deliveries*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Laporan Harian',
                    href: '<?php echo e(route("construction.reports.index")); ?>',
                    active: <?php echo e(request()->routeIs('construction.reports*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Gantt Chart',
                    href: '<?php echo e(route("projects.index")); ?>',
                    active: <?php echo e(request()->routeIs('construction.gantt*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('cosmetic') ?? false): ?>
        cosmetic: {
            title: 'Kosmetik & Beauty',
            items: [
                { section: 'Produksi' },
                {
                    label: 'Formula',
                    href: '<?php echo e(route("cosmetic.formulas.index")); ?>',
                    active: <?php echo e(request()->routeIs('cosmetic.formulas*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Batch Produksi',
                    href: '<?php echo e(route("cosmetic.batches.index")); ?>',
                    active: <?php echo e(request()->routeIs('cosmetic.batches*') ? 'true' : 'false'); ?>

                }, {
                    label: 'QC Lab',
                    href: '<?php echo e(route("cosmetic.qc.tests")); ?>',
                    active: <?php echo e(request()->routeIs('cosmetic.qc*') ? 'true' : 'false'); ?>

                },
                { section: 'Regulasi' },
                {
                    label: 'BPOM Dashboard',
                    href: '<?php echo e(route("cosmetic.bpom.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('cosmetic.bpom*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Registrasi',
                    href: '<?php echo e(route("cosmetic.registrations.index")); ?>',
                    active: <?php echo e(request()->routeIs('cosmetic.registrations*') ? 'true' : 'false'); ?>

                }, {
                    label: 'Analitik',
                    href: '<?php echo e(route("cosmetic.analytics.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('cosmetic.analytics*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>

    <?php if($navTenant?->isModuleEnabled('printing') ?? false): ?>
        printing: {
            title: 'Percetakan',
            items: [
                { section: 'Job Order' },
                {
                    label: 'Dashboard Cetak',
                    href: '<?php echo e(route("printing.dashboard")); ?>',
                    active: <?php echo e(request()->routeIs('printing.dashboard') ? 'true' : 'false'); ?>

                }, {
                    label: 'Estimasi Cetak',
                    href: '<?php echo e(route("printing.estimates")); ?>',
                    active: <?php echo e(request()->routeIs('printing.estimates*') ? 'true' : 'false'); ?>

                },
            ]
        },
    <?php endif; ?>
<?php endif; ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/layouts/_nav_industry_groups.blade.php ENDPATH**/ ?>