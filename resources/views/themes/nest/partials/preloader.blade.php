    <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="text-center">
                    <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/logorasa.png') }}" alt="Rasa Group" class="rg-preloader-logo" />
                </div>
            </div>
        </div>
    </div>
    <style>
        .rg-preloader-logo {
            max-width: 160px;
            width: min(42vw, 160px);
            height: auto;
            animation: rgPreloaderPulse 1.4s ease-in-out infinite;
        }
        @keyframes rgPreloaderPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.72; transform: scale(0.97); }
        }
    </style>
    <!-- Vendor JS-->
