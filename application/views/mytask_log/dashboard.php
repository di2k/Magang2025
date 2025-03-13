<div class="row g-3">
    <!-- Filter Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Data</h3>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="start_date">Tanggal Awal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="end_date">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ti ti-filter me-1"></i> Filter
                        </button>
                        <button type="button" id="resetFilter" class="btn btn-outline-secondary">
                            <i class="ti ti-refresh me-1"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Grafik Jumlah Akses User Berdasarkan Button ID</h3>
                <div class="card-actions">
                    <span id="last-update-info" class="text-muted me-3">
                        <i class="ti ti-clock"></i> Terakhir diperbarui: <?php echo date('d-m-Y H:i:s'); ?>
                    </span>
                    <button type="button" id="refreshNow" class="btn btn-icon" title="Refresh sekarang">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="accessChart" style="height: 400px;"></div>
            </div>
            <div class="card-footer text-muted">
                <div class="d-flex align-items-center">
                    <i class="ti ti-info-circle me-1"></i>
                    <span>Data diperbarui otomatis setiap 5 menit</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data untuk chart dari controller
        var chartData = <?php echo $chart_data; ?>;
        var buttonIds = <?php echo $button_ids; ?>;

        // Render chart
        renderChart(chartData, buttonIds);

        // Setup auto refresh setiap 5 menit (300000 ms)
        setInterval(function() {
            fetchLatestData();
        }, 300000);

        // Event listener untuk form filter
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();

            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;

            // Validasi tanggal
            if (startDate === '' || endDate === '') {
                alert('Silakan pilih tanggal awal dan akhir.');
                return;
            }

            // Kirim request AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo base_url('mytask_log/filter'); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);

                    if (response.status === 'success') {
                        // Re-render chart dengan data baru
                        renderChart(response.data, response.button_ids);
                        updateLastUpdateInfo();
                    } else {
                        alert('Terjadi kesalahan saat memfilter data.');
                    }
                }
            };

            xhr.send('start_date=' + startDate + '&end_date=' + endDate);
        });

        // Event listener untuk tombol reset
        document.getElementById('resetFilter').addEventListener('click', function() {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';

            // Reset chart ke data awal
            renderChart(chartData, buttonIds);
        });

        // Event listener untuk tombol refresh sekarang
        document.getElementById('refreshNow').addEventListener('click', function() {
            // Tambahkan animasi refresh
            var refreshIcon = document.querySelector('#refreshNow i');
            refreshIcon.classList.add('ti-loader', 'ti-spin');

            // Lakukan refresh data
            fetchLatestData();

            // Hentikan animasi setelah 1 detik
            setTimeout(function() {
                refreshIcon.classList.remove('ti-loader', 'ti-spin');
            }, 1000);
        });

        // Fungsi untuk mengambil data terbaru
        function fetchLatestData() {
            // Ambil nilai filter saat ini
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;

            var xhr = new XMLHttpRequest();

            if (startDate && endDate) {
                // Jika ada filter tanggal yang aktif, gunakan endpoint filter
                xhr.open('POST', '<?php echo base_url('mytask_log/filter'); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            renderChart(response.data, response.button_ids);
                            updateLastUpdateInfo();
                            console.log('Data refreshed with filter at ' + new Date().toLocaleTimeString());
                        }
                    }
                };
                xhr.send('start_date=' + startDate + '&end_date=' + endDate);
            } else {
                // Jika tidak ada filter, ambil semua data
                xhr.open('GET', '<?php echo base_url('mytask_log/get_latest_data'); ?>', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            renderChart(response.data, response.button_ids);
                            updateLastUpdateInfo(response.last_update);
                            console.log('Data refreshed at ' + new Date().toLocaleTimeString());
                        }
                    }
                };
                xhr.send();
            }
        }

        // Fungsi untuk memperbarui informasi waktu terakhir update
        function updateLastUpdateInfo(lastUpdateTime) {
            var lastUpdateElement = document.getElementById('last-update-info');
            var dateTime = lastUpdateTime ? new Date(lastUpdateTime) : new Date();
            var formattedDate = padZero(dateTime.getDate()) + '-' +
                padZero(dateTime.getMonth() + 1) + '-' +
                dateTime.getFullYear() + ' ' +
                padZero(dateTime.getHours()) + ':' +
                padZero(dateTime.getMinutes()) + ':' +
                padZero(dateTime.getSeconds());

            lastUpdateElement.innerHTML = '<i class="ti ti-clock"></i> Terakhir diperbarui: ' + formattedDate;
        }

        // Helper untuk format 2 digit angka
        function padZero(num) {
            return (num < 10 ? '0' : '') + num;
        }

        // Fungsi untuk merender chart
        function renderChart(data, categories) {
            // Hapus chart sebelumnya jika ada
            document.getElementById('accessChart').innerHTML = '';

            var options = {
                series: [{
                    name: 'Jumlah Akses',
                    data: data
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        }
                    }
                },
                colors: ['#206bc4'], // Tabler primary color
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 4,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    categories: categories,
                    title: {
                        text: 'Button ID'
                    },
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Akses'
                    },
                    min: 0,
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(0);
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " kali diakses";
                        }
                    }
                },
                title: {
                    text: 'Jumlah Akses User Berdasarkan Button ID',
                    align: 'center',
                    margin: 10,
                    offsetY: 20,
                    style: {
                        fontSize: '18px'
                    }
                }
            };

            var chart = new ApexCharts(document.getElementById('accessChart'), options);
            chart.render();
        }
    });
</script>