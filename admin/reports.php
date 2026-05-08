<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Reports');
?>
<div class="row g-4 mb-4">
  <div class="col-lg-8"><div class="chart-card"><h6>Monthly Parcels</h6><div style="height:300px;"><canvas id="deliveryChart"></canvas></div></div></div>
  <div class="col-lg-4"><div class="chart-card"><h6>Overview</h6><div style="height:300px;"><canvas id="parcelOverviewChart"></canvas></div></div></div>
  <div class="col-lg-6"><div class="chart-card"><h6>Top Routes</h6><div style="height:250px;"><canvas id="topRoutesChart"></canvas></div></div></div>
  <div class="col-lg-6"><div class="chart-card"><h6>Revenue</h6><div style="height:250px;"><canvas id="revenueChart"></canvas></div></div></div>
</div>
<div id="liveStatsGrid" style="display:none;"></div>
<?php spx_admin_shell_close(); ?>
