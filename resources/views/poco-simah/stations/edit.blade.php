<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Estação | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");
        * { font-family: "Inter", sans-serif; text-decoration: none; color: inherit; margin: 0; padding: 0; box-sizing: border-box; }
        body { height: 100vh; overflow: hidden; }
        .edit-container { display: flex; width: 100%; height: 100vh; }
        .edit-form-section { flex: 0 0 480px; background: #ffffff; display: flex; flex-direction: column; overflow-y: auto; }
        .form-scrollable-wrapper { flex: 1; padding: 40px; overflow-y: auto; position: relative; }
        .form-container { width: 100%; max-width: 400px; margin: auto; }
        .back-btn-container { position: absolute; top: 24px; right: 24px; z-index: 10; }
        .back-btn { display: flex; align-items: center; gap: 8px; padding: 9px 18px; background: transparent; color: #165b9c; border: 2px solid #165b9c; border-radius: 25px; font-size: 0.9rem; font-weight: 600; transition: all 0.3s; }
        .back-btn:hover { background: #165b9c; color: white; }
        .form-header { text-align: center; margin-bottom: 28px; margin-top: 20px; }
        .form-header h1 { font-size: 1.7rem; color: #333; font-weight: 700; }
        .form-header p { color: #666; font-size: 0.92rem; margin-top: 6px; }
        .form-section-header { margin: 22px 0 14px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .form-section-header h3 { font-size: 1rem; color: #333; display: flex; align-items: center; gap: 8px; }
        .form-section-header h3 i { color: #165b9c; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #444; font-size: 0.88rem; }
        .input-with-icon { position: relative; }
        .input-with-icon i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #165b9c; font-size: 0.95rem; z-index: 2; }
        .form-control { width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #ccc; border-radius: 25px; font-size: 0.92rem; transition: all 0.3s; background: #fff; color: #000; }
        .form-control:focus { outline: none; border-color: #165b9c; box-shadow: 0 0 0 3px rgba(22,91,156,0.1); }
        .toggle-group { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border: 1px solid #ccc; border-radius: 25px; background: #fff; }
        .toggle-group label { font-weight: 500; color: #444; font-size: 0.88rem; margin: 0; cursor: pointer; }
        .toggle-group input[type="checkbox"] { width: 18px; height: 18px; accent-color: #007952; cursor: pointer; }
        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #b1dfbb; }
        .alert-errors { background: #f8d7da; color: #721c24; border: 1px solid #f1b0b7; padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 0.9rem; }
        .btn-container { display: flex; gap: 12px; margin-top: 28px; }
        .submit-btn { flex: 1; padding: 14px; background: #007952; color: white; border: none; border-radius: 25px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .submit-btn:hover { background: #006b47; transform: translateY(-2px); box-shadow: 0 7px 20px rgba(0,121,82,0.3); }
        .cancel-btn { padding: 14px 24px; background: #6c757d; color: white; border: none; border-radius: 25px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; text-align: center; }
        .cancel-btn:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-import { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; background: #165b9c; color: white; border: none; border-radius: 25px; font-size: 0.92rem; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; margin-top: 10px; }
        .btn-import:hover { background: #0e4278; transform: translateY(-2px); color: white; }
        .map-hint { font-size: 0.8rem; color: #888; margin-top: 6px; padding-left: 16px; }
        .edit-map-section { flex: 1; position: relative; }
        #map { width: 100%; height: 100%; }
        .map-label { position: absolute; top: 16px; left: 50%; transform: translateX(-50%); z-index: 1000; background: rgba(22,91,156,0.85); color: white; padding: 8px 20px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; pointer-events: none; }
        @media (max-width: 900px) {
            .edit-container { flex-direction: column; }
            .edit-form-section { flex: none; overflow-y: auto; }
            .edit-map-section { height: 400px; }
            body { overflow: auto; height: auto; }
        }
    </style>
</head>
<body>
<div class="edit-container">

    <!-- Formulário -->
    <div class="edit-form-section">
        <div class="form-scrollable-wrapper">

            <div class="back-btn-container">
                <a href="{{ route('poco-simah.stations.index') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h1><i class="fas fa-tint" style="color:#165b9c;font-size:1.4rem;"></i> Editar Estação</h1>
                    <p>{{ $station->station_code }}</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert-errors">
                        <i class="fas fa-exclamation-triangle"></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('poco-simah.stations.update', $station->id) }}">
                    @csrf
                    @method('POST')

                    <div class="form-section-header">
                        <h3><i class="fas fa-info-circle"></i> Identificação</h3>
                    </div>

                    <div class="form-group">
                        <label>Nome da Estação *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-tag"></i>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $station->name) }}" required maxlength="255">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Código da Estação *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-barcode"></i>
                            <input type="text" name="station_code" class="form-control"
                                value="{{ old('station_code', $station->station_code) }}" required maxlength="100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <div class="toggle-group">
                            <input type="checkbox" name="ativa" id="ativa" value="1"
                                {{ old('ativa', $station->ativa) ? 'checked' : '' }}>
                            <label for="ativa">Estação ativa</label>
                        </div>
                    </div>

                    <div class="form-section-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Localização</h3>
                    </div>

                    <div class="form-group">
                        <label>Latitude</label>
                        <div class="input-with-icon">
                            <i class="fas fa-arrows-alt-v"></i>
                            <input type="text" name="latitude" id="latitude" class="form-control"
                                value="{{ old('latitude', $station->latitude) }}" placeholder="Ex: -12.9714">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Longitude</label>
                        <div class="input-with-icon">
                            <i class="fas fa-arrows-alt-h"></i>
                            <input type="text" name="longitude" id="longitude" class="form-control"
                                value="{{ old('longitude', $station->longitude) }}" placeholder="Ex: -38.5014">
                        </div>
                    </div>

                    <p class="map-hint"><i class="fas fa-info-circle"></i> Clique no mapa para definir a localização ou edite os campos acima.</p>

                    <div class="btn-container">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('poco-simah.stations.index') }}" class="cancel-btn">Cancelar</a>
                    </div>
                </form>

                <a href="{{ route('poco-simah.stations.import', $station->id) }}" class="btn-import">
                    <i class="fas fa-upload"></i> Importar Leituras
                </a>
            </div>
        </div>
    </div>

    <!-- Mapa -->
    <div class="edit-map-section">
        <div class="map-label"><i class="fas fa-mouse-pointer"></i> Clique no mapa para reposicionar a estação</div>
        <div id="map"></div>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var initialLat = {{ $station->latitude ?? -13.0 }};
    var initialLng = {{ $station->longitude ?? -41.5 }};
    var hasCoords  = {{ ($station->latitude && $station->longitude) ? 'true' : 'false' }};

    var map = L.map('map').setView([initialLat, initialLng], hasCoords ? 12 : 6);

    var tileSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri', maxZoom: 18
    });

    var tileStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19
    });

    tileSatellite.addTo(map);
    L.control.layers({ 'Satélite': tileSatellite, 'Mapa': tileStreet }).addTo(map);

    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var marker   = null;

    function placeMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                var pos = e.target.getLatLng();
                latInput.value = pos.lat.toFixed(7);
                lngInput.value = pos.lng.toFixed(7);
            });
        }
    }

    if (hasCoords) {
        placeMarker(initialLat, initialLng);
    }

    map.on('click', function (e) {
        placeMarker(e.latlng.lat, e.latlng.lng);
        latInput.value = e.latlng.lat.toFixed(7);
        lngInput.value = e.latlng.lng.toFixed(7);
    });

    function syncMarkerFromInputs() {
        var lat = parseFloat(latInput.value.replace(',', '.'));
        var lng = parseFloat(lngInput.value.replace(',', '.'));
        if (!isNaN(lat) && !isNaN(lng)) {
            placeMarker(lat, lng);
            map.setView([lat, lng], map.getZoom());
        }
    }

    var syncDebounce = null;
    function debouncedSync() {
        clearTimeout(syncDebounce);
        syncDebounce = setTimeout(syncMarkerFromInputs, 400);
    }

    latInput.addEventListener('input', debouncedSync);
    lngInput.addEventListener('input', debouncedSync);
</script>
</body>
</html>
