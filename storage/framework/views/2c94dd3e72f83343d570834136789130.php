<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php echo e(__('Edit Device: ') . $device->name); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <a href="<?php echo e(route('telecom.devices.show', $device)); ?>"
                    class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-arrow-left"></i>
                    <?php echo e(__('Kembali ke Detail')); ?>

                </a>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Edit Device')); ?></h1>
                <p class="text-gray-600 mt-1"><?php echo e($device->name); ?></p>
            </div>

            <?php if($errors->any()): ?>
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="<?php echo e(route('telecom.devices.update', $device)); ?>" method="POST"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- Basic Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php echo e(__('Informasi Dasar')); ?>

                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Nama Device')); ?>

                                *</label>
                            <input type="text" name="name" id="name" value="<?php echo e(old('name', $device->name)); ?>"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="device_type"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Tipe Device')); ?>

                                *</label>
                            <select name="device_type" id="device_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php echo e(__('Pilih Tipe')); ?></option>
                                <option value="router"
                                    <?php echo e(old('device_type', $device->device_type) == 'router' ? 'selected' : ''); ?>>Router
                                </option>
                                <option value="access_point"
                                    <?php echo e(old('device_type', $device->device_type) == 'access_point' ? 'selected' : ''); ?>>
                                    Access Point</option>
                                <option value="switch"
                                    <?php echo e(old('device_type', $device->device_type) == 'switch' ? 'selected' : ''); ?>>Switch
                                </option>
                                <option value="firewall"
                                    <?php echo e(old('device_type', $device->device_type) == 'firewall' ? 'selected' : ''); ?>>
                                    Firewall</option>
                            </select>
                        </div>

                        <div>
                            <label for="brand"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Brand')); ?>

                                *</label>
                            <select name="brand" id="brand" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php echo e(__('Pilih Brand')); ?></option>
                                <option value="mikrotik"
                                    <?php echo e(old('brand', $device->brand) == 'mikrotik' ? 'selected' : ''); ?>>MikroTik
                                </option>
                                <option value="ubiquiti"
                                    <?php echo e(old('brand', $device->brand) == 'ubiquiti' ? 'selected' : ''); ?>>Ubiquiti
                                </option>
                                <option value="cisco" <?php echo e(old('brand', $device->brand) == 'cisco' ? 'selected' : ''); ?>>
                                    Cisco</option>
                                <option value="openwrt"
                                    <?php echo e(old('brand', $device->brand) == 'openwrt' ? 'selected' : ''); ?>>OpenWRT</option>
                                <option value="other" <?php echo e(old('brand', $device->brand) == 'other' ? 'selected' : ''); ?>>
                                    Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="model"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Model')); ?></label>
                            <input type="text" name="model" id="model"
                                value="<?php echo e(old('model', $device->model)); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Connection Settings -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php echo e(__('Pengaturan Koneksi')); ?>

                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="ip_address"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('IP Address')); ?>

                                *</label>
                            <input type="text" name="ip_address" id="ip_address"
                                value="<?php echo e(old('ip_address', $device->ip_address)); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                        </div>

                        <div>
                            <label for="port"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Port')); ?></label>
                            <input type="number" name="port" id="port"
                                value="<?php echo e(old('port', $device->port)); ?>" min="1" max="65535"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="username"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Username')); ?>

                                *</label>
                            <input type="text" name="username" id="username"
                                value="<?php echo e(old('username', $device->username)); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="password"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Password')); ?></label>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="<?php echo e(__('Kosongkan jika tidak ingin mengubah')); ?>">
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo e(__('Biarkan kosong untuk mempertahankan password lama')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php echo e(__('Informasi Tambahan')); ?>

                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="parent_device_id"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Parent Device')); ?></label>
                            <select name="parent_device_id" id="parent_device_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php echo e(__('Tidak ada (Root Device)')); ?></option>
                                <?php $__currentLoopData = $parentDevices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($parent->id); ?>"
                                        <?php echo e(old('parent_device_id', $device->parent_device_id) == $parent->id ? 'selected' : ''); ?>>
                                        <?php echo e($parent->name); ?> (<?php echo e($parent->ip_address); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Status')); ?></label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="online"
                                    <?php echo e(old('status', $device->status) == 'online' ? 'selected' : ''); ?>>Online</option>
                                <option value="offline"
                                    <?php echo e(old('status', $device->status) == 'offline' ? 'selected' : ''); ?>>Offline
                                </option>
                                <option value="maintenance"
                                    <?php echo e(old('status', $device->status) == 'maintenance' ? 'selected' : ''); ?>>Maintenance
                                </option>
                                <option value="pending"
                                    <?php echo e(old('status', $device->status) == 'pending' ? 'selected' : ''); ?>>Pending
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="location"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Lokasi')); ?></label>
                            <input type="text" name="location" id="location"
                                value="<?php echo e(old('location', $device->location)); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: Tower A - Jakarta Selatan">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('Koordinat GPS (Opsional)')); ?></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <div>
                                    <input type="number" name="latitude" id="latitude"
                                        value="<?php echo e(old('latitude', $device->latitude)); ?>" step="0.000001"
                                        placeholder="Latitude (-90 to 90)"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <input type="number" name="longitude" id="longitude"
                                        value="<?php echo e(old('longitude', $device->longitude)); ?>" step="0.000001"
                                        placeholder="Longitude (-180 to 180)"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <button type="button" id="getLocationBtn"
                                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo e(__('Get Current Location')); ?>

                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">
                                <?php echo e(__('Klik tombol di atas untuk mendapatkan koordinat dari browser, atau masukkan manual')); ?>

                            </p>

                            <!-- Mini Map Preview -->
                            <div id="mapPreview" class="border border-gray-300 rounded-lg"
                                style="height: 250px; display: none;"></div>
                            <div id="mapPlaceholder"
                                class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50">
                                <i class="fas fa-map mx-auto h-12 w-12 text-gray-400"></i>
                                <p class="mt-2 text-sm text-gray-500">
                                    <?php echo e(__('Map akan muncul setelah koordinat diisi')); ?></p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="coverage_radius"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Coverage Radius (meter)')); ?></label>
                            <input type="number" name="coverage_radius" id="coverage_radius"
                                value="<?php echo e(old('coverage_radius', $device->coverage_radius)); ?>" min="1"
                                max="50000"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: 1000 (1 km)">
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo e(__('Radius coverage area dalam meter (1-50000). Kosongkan jika tidak diketahui.')); ?>

                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Deskripsi')); ?></label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo e(old('description', $device->description)); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <a href="<?php echo e(route('telecom.devices.show', $device)); ?>"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        <?php echo e(__('Batal')); ?>

                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <?php echo e(__('Update Device')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('styles'); ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('scripts'); ?>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let map = null;
                let marker = null;
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const getLocationBtn = document.getElementById('getLocationBtn');
                const mapPreview = document.getElementById('mapPreview');
                const mapPlaceholder = document.getElementById('mapPlaceholder');

                function initMap(lat, lng) {
                    if (map) {
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                        return;
                    }

                    mapPreview.style.display = 'block';
                    mapPlaceholder.style.display = 'none';

                    map = L.map('mapPreview').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);

                    marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(map);

                    marker.on('dragend', function(event) {
                        const position = marker.getLatLng();
                        latInput.value = position.lat.toFixed(7);
                        lngInput.value = position.lng.toFixed(7);
                    });
                }

                function updateMap() {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);

                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        initMap(lat, lng);
                    }
                }

                getLocationBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        alert('<?php echo e(__('Geolocation tidak didukung oleh browser Anda')); ?>');
                        return;
                    }

                    getLocationBtn.disabled = true;
                    getLocationBtn.innerHTML =
                        '<i class="fas fa-spinner fa-spin"></i> <?php echo e(__('Getting location...')); ?>';

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latInput.value = position.coords.latitude.toFixed(7);
                            lngInput.value = position.coords.longitude.toFixed(7);
                            updateMap();
                            getLocationBtn.disabled = false;
                            getLocationBtn.innerHTML =
                                '<i class="fas fa-map-marker-alt"></i> <?php echo e(__('Get Current Location')); ?>';
                        },
                        function(error) {
                            alert('<?php echo e(__('Gagal mendapatkan lokasi: ')); ?>' + error.message);
                            getLocationBtn.disabled = false;
                            getLocationBtn.innerHTML =
                                '<i class="fas fa-map-marker-alt"></i> <?php echo e(__('Get Current Location')); ?>';
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                });

                latInput.addEventListener('change', updateMap);
                lngInput.addEventListener('change', updateMap);

                if (latInput.value && lngInput.value) {
                    updateMap();
                }
            });
        </script>
    <?php $__env->stopPush(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\devices\edit.blade.php ENDPATH**/ ?>