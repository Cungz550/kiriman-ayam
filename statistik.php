<?php
session_start();
include 'db.php';
// Biar PDO bisa dipakai, bikin koneksi PDO dari config MySQLi
$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi PDO gagal: " . $e->getMessage());
}
include 'partials/header.php';

// Redirect ke login kalau belum login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Query untuk mendapatkan data statistik
try {
    // Total kiriman
    $stmt_total = $pdo->query("SELECT COUNT(*) as total FROM kiriman");
    $total_kiriman = $stmt_total->fetch()['total'];
    
    // Kiriman hari ini
    $stmt_today = $pdo->query("SELECT COUNT(*) as today FROM kiriman WHERE DATE(created_at) = CURDATE()");
    $kiriman_today = $stmt_today->fetch()['today'];
    
    // Kiriman minggu ini
    $stmt_week = $pdo->query("SELECT COUNT(*) as week FROM kiriman WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
    $kiriman_week = $stmt_week->fetch()['week'];
    
    // Top 5 destinasi
    $stmt_top_dest = $pdo->query("
        SELECT nama_kiriman, COUNT(*) as total 
        FROM kiriman 
        GROUP BY nama_kiriman 
        ORDER BY total DESC 
        LIMIT 5
    ");
    $top_destinations = $stmt_top_dest->fetchAll();
    
    // Data untuk chart per bulan (6 bulan terakhir)
    $stmt_monthly = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as bulan,
            DATE_FORMAT(created_at, '%M %Y') as bulan_nama,
            COUNT(*) as total
        FROM kiriman 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY bulan ASC
    ");
    $monthly_data = $stmt_monthly->fetchAll();
    
    // Data untuk chart per kota
    $stmt_city_chart = $pdo->query("
        SELECT nama_kiriman, COUNT(*) as total 
        FROM kiriman 
        GROUP BY nama_kiriman 
        ORDER BY total DESC 
        LIMIT 10
    ");
    $city_chart_data = $stmt_city_chart->fetchAll();
    
} catch (PDOException $e) {
    // Fallback data jika database error
    $total_kiriman = 0;
    $kiriman_today = 0;
    $kiriman_week = 0;
    $top_destinations = [];
    $monthly_data = [];
    $city_chart_data = [];
}
?>

<div class="container">
    <div class="header-section">
        <h1 class="page-title">📊 Dashboard Statistik</h1>
        <p class="subtitle">Analytics dan insights untuk data kiriman</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3 class="stat-number"><?php echo number_format($total_kiriman); ?></h3>
                <p class="stat-label">Total Kiriman</p>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
                <h3 class="stat-number"><?php echo number_format($kiriman_today); ?></h3>
                <p class="stat-label">Hari Ini</p>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <h3 class="stat-number"><?php echo number_format($kiriman_week); ?></h3>
                <p class="stat-label">Minggu Ini</p>
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon">🌟</div>
            <div class="stat-content">
                <h3 class="stat-number"><?php echo isset($top_destinations[0]['nama_kiriman']) ? htmlspecialchars($top_destinations[0]['nama_kiriman']) : '-'; ?></h3>
                <p class="stat-label">Destinasi Terfavorit</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Monthly Trend Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>📈 Trend Bulanan</h3>
                <p>Perkembangan kiriman 6 bulan terakhir</p>
            </div>
            <canvas id="monthlyChart"></canvas>
        </div>
        
        <!-- Top Destinations Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>🏆 Top Destinasi</h3>
                <p>Kota dengan kiriman terbanyak</p>
            </div>
            <canvas id="destinationChart"></canvas>
        </div>
    </div>

    <!-- Top Destinations Table -->
    <div class="table-card">
        <div class="table-header">
            <h3>🎯 Top 5 Destinasi</h3>
            <p>Ranking kota berdasarkan jumlah kiriman</p>
        </div>
        <div class="table-container">
            <?php if (!empty($top_destinations)): ?>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Destinasi</th>
                            <th>Total Kiriman</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_destinations as $index => $dest): ?>
                            <tr>
                                <td>
                                    <span class="rank-badge rank-<?php echo $index + 1; ?>">
                                        #<?php echo $index + 1; ?>
                                    </span>
                                </td>
                                <td class="destination-name">
                                    <strong><?php echo htmlspecialchars($dest['nama_kiriman']); ?></strong>
                                </td>
                                <td class="total-count">
                                    <?php echo number_format($dest['total']); ?>
                                </td>
                                <td class="percentage">
                                    <?php 
                                    $percentage = $total_kiriman > 0 ? ($dest['total'] / $total_kiriman) * 100 : 0;
                                    echo number_format($percentage, 1); 
                                    ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state" style="min-height:unset;padding:40px 0;">
                    <div class="empty-icon">📭</div>
                    <p>Belum ada data kiriman untuk ditampilkan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div class="action-buttons">
        <a href="index.php" class="btn-back">
            <span class="icon">⬅️</span>
            Kembali ke Dashboard
        </a>
        <a href="riwayat.php" class="btn-secondary">
            <span class="icon">📚</span>
            Lihat Riwayat
        </a>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.header-section {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    color: #1e40af;
    font-size: 2.5em;
    margin-bottom: 10px;
    font-weight: bold;
}

.subtitle {
    color: #6b7280;
    font-size: 1.1em;
    margin-bottom: 0;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border-left: 4px solid;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card.primary { border-left-color: #3b82f6; }
.stat-card.success { border-left-color: #10b981; }
.stat-card.warning { border-left-color: #f59e0b; }
.stat-card.info { border-left-color: #8b5cf6; }

.stat-icon {
    font-size: 2.5em;
    opacity: 0.8;
}

.stat-number {
    font-size: 2.2em;
    font-weight: bold;
    color: #1f2937;
    margin: 0 0 4px 0;
}

.stat-label {
    color: #6b7280;
    font-size: 0.9em;
    margin: 0;
    font-weight: 500;
}

/* Charts */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.chart-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.chart-header {
    margin-bottom: 20px;
}

.chart-header h3 {
    color: #1f2937;
    margin: 0 0 8px 0;
    font-size: 1.3em;
}

.chart-header p {
    color: #6b7280;
    margin: 0;
    font-size: 0.9em;
}

/* Table */
.table-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
}

.table-header {
    margin-bottom: 20px;
}

.table-header h3 {
    color: #1f2937;
    margin: 0 0 8px 0;
    font-size: 1.3em;
}

.table-header p {
    color: #6b7280;
    margin: 0;
    font-size: 0.9em;
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
}

.stats-table th,
.stats-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.stats-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.rank-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: bold;
    color: white;
}

.rank-1 { background: #ffd700; color: #92400e; }
.rank-2 { background: #c0c0c0; color: #374151; }
.rank-3 { background: #cd7f32; color: white; }
.rank-4, .rank-5 { background: #6b7280; }

.destination-name {
    color: #1f2937;
}

.total-count {
    font-weight: 600;
    color: #059669;
}

.percentage {
    color: #6b7280;
    font-size: 0.9em;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.empty-icon {
    font-size: 3em;
    margin-bottom: 16px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-back, .btn-secondary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 2px solid #d1d5db;
}

.btn-secondary:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-card {
        padding: 16px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .page-title {
        font-size: 2em;
    }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
// Data untuk charts dari PHP
const monthlyData = <?php echo json_encode($monthly_data); ?>;
const cityData = <?php echo json_encode($city_chart_data); ?>;

// Monthly Trend Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: monthlyData.map(item => item.bulan_nama || 'N/A'),
        datasets: [{
            label: 'Jumlah Kiriman',
            data: monthlyData.map(item => item.total),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Top Destinations Chart
const destCtx = document.getElementById('destinationChart').getContext('2d');
new Chart(destCtx, {
    type: 'doughnut',
    data: {
        labels: cityData.map(item => item.nama_kiriman),
        datasets: [{
            data: cityData.map(item => item.total),
            backgroundColor: [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
            ],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    font: {
                        size: 12
                    }
                }
            }
        }
    }
});

// Auto-refresh data setiap 5 menit
setInterval(() => {
    location.reload();
}, 300000);
</script>

<?php include 'partials/footer.php'; ?>