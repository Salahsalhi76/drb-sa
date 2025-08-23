@extends('admin.layouts.app')

@section('title', 'Map View')

@section('content')

    @php
        $value = web_map_settings();
    @endphp
    @if ($value == 'google')
        <style>
            #map {
                height: 70vh;
                margin: 15px;
            }

            #legend {
                font-family: Arial, sans-serif;
                background: #fff;
                padding: 10px;
                margin: 10px;
                border: 3px solid #000;
            }

            #legend h3 {
                margin-top: 0;
            }

            #legend img {
                vertical-align: middle;
            }
        </style>
        <!-- Start Page content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3>{{ $page }}</h3>
                        </div>
                        <div id="map"></div>

                        <div id="legend">
                            <h3>@lang('view_pages.legend')</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script type="text/javascript"
            src="https://maps.google.com/maps/api/js?key={{ get_settings('google_map_key') }}&libraries=visualization"></script>

        <!-- Firebase JS SDK -->
        <script src="https://www.gstatic.com/firebasejs/7.19.0/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/7.19.0/firebase-database.js"></script>
        <script src="https://www.gstatic.com/firebasejs/7.19.0/firebase-analytics.js"></script>

        <script type="text/javascript">
            var default_lat = '{{ $default_lat }}';
            var default_lng = '{{ $default_lng }}';
            var company_key = '{{ auth()->user()->company_key }}';
            var markers = {}; // We'll use an object to map drivers to markers by driver ID.

            var firebaseConfig = {
                apiKey: "{{ get_settings('firebase-api-key') }}",
                authDomain: "{{ get_settings('firebase-auth-domain') }}",
                databaseURL: "{{ get_settings('firebase-db-url') }}",
                projectId: "{{ get_settings('firebase-project-id') }}",
                storageBucket: "{{ get_settings('firebase-storage-bucket') }}",
                messagingSenderId: "{{ get_settings('firebase-messaging-sender-id') }}",
                appId: "{{ get_settings('firebase-app-id') }}",
                measurementId: "{{ get_settings('firebase-measurement-id') }}"
            };

            // Initialize Firebase
            firebase.initializeApp(firebaseConfig);
            firebase.analytics();

            var tripRef = firebase.database().ref('drivers');

            var map = new google.maps.Map(document.getElementById('map'), {
                center: new google.maps.LatLng(default_lat, default_lng),
                zoom: 5,
                mapTypeId: 'roadmap'
            });

            var iconBase = '{{ asset('map/icon/') }}';

            var icons = {
                car_available: {
                    name: 'Available',
                    icon: iconBase + '/driver_available.png'
                },
                car_ontrip: {
                    name: 'OnTrip',
                    icon: iconBase + '/driver_on_trip.png'
                },
                car_offline: {
                    name: 'Offline',
                    icon: iconBase + '/driver_off_trip.png'
                },
                bike_available: {
                    name: 'Available',
                    icon: iconBase + '/available-bike.png'
                },
                bike_ontrip: {
                    name: 'OnTrip',
                    icon: iconBase + '/ontrip-bike.png'
                },
                bike_offline: {
                    name: 'Offline',
                    icon: iconBase + '/offline-bike.png'
                },
                truck_available: {
                    name: 'Available',
                    icon: iconBase + '/available-truck.png'
                },
                truck_ontrip: {
                    name: 'OnTrip',
                    icon: iconBase + '/ontrip-truck.png'
                },
                truck_offline: {
                    name: 'Offline',
                    icon: iconBase + '/offline-truck.png'
                },
            };

            var fliter_icons = {
                available: {
                    name: 'Available',
                    icon: iconBase + '/available.png'
                },
                ontrip: {
                    name: 'OnTrip',
                    icon: iconBase + '/ontrip.png'
                },
                offline: {
                    name: 'Offline',
                    icon: iconBase + '/offline.png'
                }
            };

            var legend = document.getElementById('legend');
            for (var key in fliter_icons) {
                var type = fliter_icons[key];
                var name = type.name;
                var icon = type.icon;
                var div = document.createElement('div');
                div.innerHTML = '<img src="' + icon + '"> ' + name;
                legend.appendChild(div);
            }
            map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);

            // Listening for Firebase child events
            tripRef.on('child_added', function(snapshot) {
                var newData = snapshot.val();
                addDriverIcon(snapshot.key, newData);
            });

            tripRef.on('child_changed', function(snapshot) {
                var updatedData = snapshot.val();
                updateDriverIcon(snapshot.key, updatedData);
            });

            tripRef.on('child_removed', function(snapshot) {
                var removedData = snapshot.val();
                removeDriverIcon(snapshot.key);
            });

            // Add a new driver icon to the map
            function addDriverIcon(driverId, data) {
                if (typeof data.l !== 'undefined') {
                    var iconImg = getDriverIcon(data);
                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(data.l[0], data.l[1]),
                        icon: iconImg,
                        map: map
                    });

                    // Adding a click listener for displaying information
                    var infowindow = new google.maps.InfoWindow({
                        content: `<div class="p-2">
                                    <h6><i class="fa fa-id-badge"></i> : ${data.name ?? '-'}</h6>
                                    <h6><i class="fa fa-phone-square"></i> : ${data.mobile ?? '-'}</h6>
                                    <h6><i class="fa fa-id-card"></i> : ${data.vehicle_number ?? '-'}</h6>
                                    <h6><i class="fa fa-truck"></i> : ${data.vehicle_type_name ?? '-'}</h6>
                                  </div>`
                    });

                    marker.addListener('click', function() {
                        infowindow.open(map, marker);
                    });

                    // Store the marker by driverId
                    markers[driverId] = marker;
                }
            }

            // Update an existing driver icon on the map
            function updateDriverIcon(driverId, data) {
                // First remove the existing marker
                removeDriverIcon(driverId);
                // Then add the updated marker
                addDriverIcon(driverId, data);
            }

            // Remove a driver icon from the map
            function removeDriverIcon(driverId) {
                if (markers[driverId]) {
                    markers[driverId].setMap(null); // Remove the marker from the map
                    delete markers[driverId]; // Remove it from the markers object
                }
            }

            // Helper function to determine which icon to use
            function getDriverIcon(data) {
                var date = new Date();
                var timestamp = date.getTime();
                var conditional_timestamp = new Date(timestamp - 5 * 60000); // 5 minutes threshold

                if (conditional_timestamp > data.updated_at) {


                    return icons['car_offline'].icon


                } else {
                    // Driver is either available or on a trip
                    if (data.is_available && data.is_active) {
                        return icons['car_available'].icon;
                    } else if (data.is_active && !data.is_available) {
                        return icons['car_ontrip'].icon;
                    } else {
                        return icons['car_offline'].icon;
                    }
                }
            }
        </script>
    @elseif($value == 'open_street')
        <!-- Add your open_street map logic here if needed -->
    @endif
@endsection
