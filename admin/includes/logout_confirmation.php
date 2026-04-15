<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 logout-modal">

            <div class="modal-body text-center px-4 py-5">

                <!-- Icon -->
                <div class="logout-icon mb-4">
                    <div class="pulse-ring"></div>
                    <i class="bi bi-power"></i>
                </div>

                <!-- Text -->
                <h4 class="fw-semibold mb-2">Confirm Logout</h4>
                <p class="text-muted mb-4">
                    Are you sure you want to logout from your account?
                </p>

                <!-- Buttons -->
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-secondary px-4"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <a href="../logout.php" class="btn btn-danger px-4">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

<style>
    .logout-modal {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* --- Glowing Icon Area --- */
    .logout-icon {
        position: relative;
        width: 80px;
        height: 80px;
        background: rgba(220, 53, 69, 0.1);
        color: #f9061a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 38px;
        margin: 0 auto;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .pulse-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px solid rgba(199, 9, 28, 0.5);
        animation: modalPulse 2s infinite;
    }

    .logout-modal .btn {
        border-radius: 30px;
        min-width: 120px;
    }

    .modal.fade .modal-dialog {
        transform: scale(0.9);
    }

    .modal.fade.show .modal-dialog {
        transform: scale(1);
    }

    @keyframes modalPulse {
        0% {
            transform: scale(1);
            opacity: 0.6;
        }

        70% {
            transform: scale(1.4);
            opacity: 0;
        }

        100% {
            opacity: 0;
        }
    }

    .logout-modal .btn-danger {
        transition: all 0.2s ease;
    }

    .logout-modal .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
    }
</style>