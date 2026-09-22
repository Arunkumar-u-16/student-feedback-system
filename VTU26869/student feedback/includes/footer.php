<?php if (!isset($noSidebar) || !$noSidebar): ?>
    </div> <!-- End content -->
    
    <!-- Mobile Bottom Navigation -->
    <div class="navbar-bottom d-flex d-lg-none">
        <a href="<?php echo BASE_URL; ?><?php echo $_SESSION['role']; ?>/dashboard.php" class="nav-item-bottom <?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-house fs-5"></i>
            <span>Home</span>
        </a>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="<?php echo BASE_URL; ?>admin/reports.php" class="nav-item-bottom <?php echo ($activePage == 'reports') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line fs-5"></i>
            <span>Reports</span>
        </a>
        <?php elseif ($_SESSION['role'] === 'faculty'): ?>
        <a href="<?php echo BASE_URL; ?>faculty/reports.php" class="nav-item-bottom <?php echo ($activePage == 'reports') ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie fs-5"></i>
            <span>Analytics</span>
        </a>
        <?php elseif ($_SESSION['role'] === 'student'): ?>
        <a href="<?php echo BASE_URL; ?>student/dashboard.php" class="nav-item-bottom <?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-file-pen fs-5"></i>
            <span>Feedback</span>
        </a>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>mobile_access.php" class="nav-item-bottom">
            <i class="fas fa-qrcode fs-5"></i>
            <span>Link</span>
        </a>

        <a href="#" class="nav-item-bottom" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="fas fa-bars fs-5"></i>
            <span>Menu</span>
        </a>
        
    </div>
</div> <!-- End wrapper -->
<?php
endif; ?>

<!-- Mobile Link Modal -->
<div class="modal fade" id="mobileLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <?php 
                    $detectedIp = getHostByName(getHostName());
                    $localIp = ($detectedIp !== '127.0.0.1') ? $detectedIp : "192.168.29.151"; 
                    $url = "http://" . $localIp . "/student%20feedback/";
                    $qr = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url);
                ?>
                <div class="mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="fas fa-mobile-screen-button text-primary fs-3"></i>
                    </div>
                    <h5 class="fw-bold">Mobile Quick Access</h5>
                    <p class="text-muted small">Access this system on your smartphone</p>
                </div>

                <div class="bg-white p-3 rounded-4 shadow-sm d-inline-block mb-4 border">
                    <img src="<?php echo $qr; ?>" alt="QR Code" style="width: 160px; height: 160px;">
                </div>

                <div class="text-start mb-4">
                    <label class="form-label small fw-semibold text-secondary">Shareable Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" id="globalMobileLink" value="<?php echo $url; ?>" readonly>
                        <button class="btn btn-primary" onclick="copyGlobalMobileLink(event)">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <a href="https://wa.me/?text=<?php echo urlencode($url); ?>" target="_blank" class="btn btn-success rounded-pill py-2">
                        <i class="fab fa-whatsapp me-2"></i> Share via WhatsApp
                    </a>
                </div>
                <p class="mt-3 small text-muted">Make sure both devices are on the same Wi-Fi network.</p>
            </div>
        </div>
    </div>
</div>

<script>
function copyGlobalMobileLink(event) {
    var copyText = document.getElementById("globalMobileLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    let btn = event.currentTarget;
    let originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.replace('btn-primary', 'btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.replace('btn-success', 'btn-primary');
    }, 2000);
}
</script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <?php if (isset($extraJS))
    echo $extraJS; ?>
</body>
</html>
