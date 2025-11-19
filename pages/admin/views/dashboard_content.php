        <!-- DASHBOARD CONTENT -->
        <div class="dashboard-box">
            <h3 class="mb-4"><i class="fa-solid fa-chart-line"></i> Dashboard tổng quan</h3>

            <!-- THỐNG KÊ TỔNG QUAN -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_candidates']; ?></div>
                                <div class="stat-label">Ứng viên</div>
                            </div>
                            <i class="fas fa-users fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_recruiters']; ?></div>
                                <div class="stat-label">Nhà tuyển dụng</div>
                            </div>
                            <i class="fas fa-user-tie fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_companies']; ?></div>
                                <div class="stat-label">Công ty</div>
                            </div>
                            <i class="fas fa-building fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_jobs']; ?></div>
                                <div class="stat-label">Tin tuyển dụng</div>
                            </div>
                            <i class="fas fa-briefcase fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card danger">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats['active_jobs']; ?>/<?php echo $stats['pending_jobs']; ?></div>
                                <div class="stat-label">Đang hiển thị / Chờ duyệt</div>
                            </div>
                            <i class="fas fa-eye fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card" style="background: #6c757d;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_applications']; ?></div>
                                <div class="stat-label">Hồ sơ ứng tuyển</div>
                            </div>
                            <i class="fas fa-file-alt fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BIỂU ĐỒ -->
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Trạng thái tin tuyển dụng theo tháng</h5>
                        <canvas id="jobsChart" width="400" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5 class="mb-3"><i class="fas fa-chart-line"></i> Số lượng ứng tuyển theo tháng</h5>
                        <canvas id="applicationsChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

    <script>
        // Dữ liệu cho biểu đồ tin tuyển dụng
        const jobsData = <?php echo json_encode($jobs_chart_data); ?>;

        // Biểu đồ trạng thái tin tuyển dụng
        const jobsCtx = document.getElementById('jobsChart').getContext('2d');
        new Chart(jobsCtx, {
            type: 'bar',
            data: {
                labels: jobsData.labels,
                datasets: [{
                    label: 'Đang tuyển',
                    data: jobsData.active,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }, {
                    label: 'Chờ duyệt',
                    data: jobsData.pending,
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }, {
                    label: 'Tạm dừng',
                    data: jobsData.inactive,
                    backgroundColor: 'rgba(108, 117, 125, 0.8)',
                    borderColor: 'rgba(108, 117, 125, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Dữ liệu cho biểu đồ ứng tuyển
        const appsData = <?php echo json_encode($applications_chart_data); ?>;

        // Biểu đồ số lượng ứng tuyển
        const appsCtx = document.getElementById('applicationsChart').getContext('2d');
        new Chart(appsCtx, {
            type: 'line',
            data: {
                labels: appsData.labels,
                datasets: [{
                    label: 'Số lượng ứng tuyển',
                    data: appsData.values,
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>