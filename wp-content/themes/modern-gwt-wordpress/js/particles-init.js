// Particles init (optimized) supporting multiple types: "network" and "helix".
// Backward-compatible with #particles-js-network and #particles-js-helix containers.
// Enhanced for cross-device performance: Android, iOS, MacBooks, Laptops
(function () {
    const deviceInfo = (() => {
        const ua = navigator.userAgent?.toLowerCase() || '';
        const isMobileDevice = /mobile|android|iphone|ipad|ipod|tablet|touch/i.test(ua);

        // Pointer detection (more reliable than UA)
        const isCoarsePointer = window.matchMedia?.('(pointer: coarse)').matches || false;
        const isFinePointer = window.matchMedia?.('(pointer: fine)').matches || false;

        // Screen size classification
        const maxWidth = window.screen.availWidth || window.innerWidth;
        const isMobileScreen = maxWidth <= 768;
        const isTabletScreen = maxWidth > 768 && maxWidth <= 1024;
        const isDesktopScreen = maxWidth > 1024;

        // Detect specific OS
        const isAndroid = /android/.test(ua);
        const isIOS = /iphone|ipad|ipod/.test(ua);
        const isMac = /macintosh|mac os x/.test(ua);
        const isWindows = /win/.test(ua);
        const isLinux = /linux/.test(ua);

        // Performance indicators
        const cores = navigator.hardwareConcurrency || 2;
        const hasAnimationFrame = !!window.requestAnimationFrame;
        const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches || false;
        const prefersDarkMode = window.matchMedia?.('(prefers-color-scheme: dark)').matches || false;
        const lowPowerMode = navigator.getBattery?.() || null;

        // GPU acceleration via canvas
        const gl = (() => {
            try {
                const canvas = document.createElement('canvas');
                return canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            } catch { return null; }
        })();
        const hasGPU = !!gl;

        // Determine device type
        let deviceType = 'desktop';
        if (isAndroid) deviceType = 'android';
        else if (isIOS) deviceType = 'ios';
        else if (isMac) deviceType = 'macos';
        else if (isWindows || isLinux) deviceType = 'desktop';

        const isMobile = isMobileDevice || isCoarsePointer || isMobileScreen;

        return {
            isMobile,
            isMobileDevice,
            isTabletScreen,
            isDesktopScreen,
            isAndroid,
            isIOS,
            isMac,
            deviceType,
            cores,
            hasGPU,
            prefersReducedMotion,
            prefersDarkMode,
            screenWidth: maxWidth
        };
    })();

    const perfProfile = (() => {
        // Low-end device detection
        const isLowEnd = () => {
            // Assume low-end if: old device or low core count
            return deviceInfo.cores <= 2 && deviceInfo.isAndroid;
        };

        // Mid-range device detection
        const isMidRange = () => {
            return deviceInfo.cores >= 4 && deviceInfo.isAndroid || deviceInfo.isIOS;
        };

        // High-end device detection
        const isHighEnd = () => {
            return deviceInfo.cores >= 8 || (deviceInfo.screenWidth >= 1440 && deviceInfo.isMac);
        };

        const reduceMotion = deviceInfo.prefersReducedMotion;

        return {
            isLowEnd: isLowEnd(),
            isMidRange: isMidRange(),
            isHighEnd: isHighEnd(),
            reduceMotion
        };
    })();

    function getNetworkConfig() {
        let particleCount = 200;
        let speed = 1;
        let lineDistance = 150;
        let lineOpacity = 0.35;
        let particleSize = 5;
        let enableLines = true;
        let targetFPS = 60;

        // Android optimization
        if (deviceInfo.isAndroid) {
            if (perfProfile.isLowEnd) {
                particleCount = 40;
                speed = 0.3;
                lineDistance = 80;
                lineOpacity = 0.2;
                particleSize = 2;
                enableLines = false;
                targetFPS = 20;
            } else if (perfProfile.isMidRange) {
                particleCount = 80;
                speed = 0.5;
                lineDistance = 120;
                lineOpacity = 0.25;
                particleSize = 3;
                enableLines = true;
                targetFPS = 30;
            }
        }
        // iOS optimization
        else if (deviceInfo.isIOS) {
            if (perfProfile.isLowEnd) {
                particleCount = 60;
                speed = 0.4;
                lineDistance = 100;
                lineOpacity = 0.2;
                particleSize = 3;
                enableLines = false;
                targetFPS = 24;
            } else if (perfProfile.isMidRange) {
                particleCount = 120;
                speed = 0.6;
                lineDistance = 130;
                lineOpacity = 0.3;
                particleSize = 4;
                enableLines = true;
                targetFPS = 30;
            }
        }
        // MacOS optimization
        else if (deviceInfo.isMac) {
            if (perfProfile.isHighEnd) {
                particleCount = 350;
                speed = 0.4;
                lineDistance = 180;
                lineOpacity = 0.4;
                particleSize = 6;
                enableLines = true;
                targetFPS = 60;
            } else {
                particleCount = 250;
                speed = 0.4;
                lineDistance = 160;
                lineOpacity = 0.35;
                particleSize = 5;
                enableLines = true;
                targetFPS = 50;
            }
        }
        // Desktop/Laptop (Windows/Linux) optimization
        else {
            if (perfProfile.isHighEnd) {
                particleCount = 300;
                speed = 0.4;
                lineDistance = 180;
                lineOpacity = 0.4;
                particleSize = 6;
                enableLines = true;
                targetFPS = 60;
            } else {
                particleCount = 200;
                speed = 0.3;
                lineDistance = 150;
                lineOpacity = 0.35;
                particleSize = 5;
                enableLines = true;
                targetFPS = 60;
            }
        }

        // Respect prefers-reduced-motion
        if (deviceInfo.prefersReducedMotion) {
            speed *= 0.5;
            particleCount = Math.floor(particleCount * 0.4);
            enableLines = false;
        }

        return {
            particleCount,
            speed,
            lineDistance,
            lineOpacity,
            particleSize,
            enableLines,
            targetFPS,
            densityArea: 800
        };
    }

    function initNetwork(containerId, overrides) {
        if (typeof particlesJS !== "function") return null;

        const netCfg = getNetworkConfig();

        const cfg = {
            particles: {
                number: { value: netCfg.particleCount, density: { enable: true, value_area: netCfg.densityArea } },
                color: { value: "#235F2A" },
                shape: { type: "circle", stroke: { width: 0, color: "#000000" }, polygon: { nb_sides: 5 } },
                opacity: { value: 0.5, random: false },
                size: { value: netCfg.particleSize, random: true },
                line_linked: { enable: netCfg.enableLines, distance: netCfg.lineDistance, color: "#acc638", opacity: netCfg.lineOpacity, width: 1 },
                move: { enable: true, speed: netCfg.speed, direction: "none", random: true, straight: false, out_mode: "out", bounce: false }
            },
            interactivity: {
                detect_on: "canvas",
                events: { onclick: { enable: true, mode: "push" }, resize: true },
                modes: {
                    grab: { distance: 400, line_linked: { opacity: 1 } },
                    bubble: { distance: 400, size: 40, duration: 2, opacity: 8, speed: 3 },
                    repulse: { distance: 200, duration: 0.4 },
                    push: { particles_nb: 1 },
                    remove: { particles_nb: 2 }
                }
            },
            retina_detect: !deviceInfo.isMobile
        };

        // Apply shallow overrides if provided
        if (overrides && typeof overrides === "object") {
            Object.assign(cfg.particles, overrides.particles || {});
            Object.assign(cfg.interactivity || {}, overrides.interactivity || {});
        }

        particlesJS(containerId, cfg);
        return function dispose() { /* particles.js does not expose dispose per instance reliably */ };
    }

    function getFlyAroundConfig() {
        let particleCount = 80;
        let targetFPS = 60;
        let orbitRadius = 120;
        let orbitSpeed = 0.5;
        let particleSize = 3;
        let randomness = 0.3;
        let verticalSpread = 50;

        // Android optimization
        if (deviceInfo.isAndroid) {
            if (perfProfile.isLowEnd) {
                particleCount = 20;
                targetFPS = 20;
                orbitRadius = 80;
                orbitSpeed = 0.3;
                particleSize = 2;
                randomness = 0.2;
                verticalSpread = 30;
            } else if (perfProfile.isMidRange) {
                particleCount = 40;
                targetFPS = 30;
                orbitRadius = 100;
                orbitSpeed = 0.4;
                particleSize = 2.5;
                randomness = 0.25;
                verticalSpread = 40;
            }
        }
        // iOS optimization
        else if (deviceInfo.isIOS) {
            if (perfProfile.isLowEnd) {
                particleCount = 25;
                targetFPS = 24;
                orbitRadius = 90;
                orbitSpeed = 0.35;
                particleSize = 2.5;
                randomness = 0.2;
                verticalSpread = 35;
            } else if (perfProfile.isMidRange) {
                particleCount = 50;
                targetFPS = 30;
                orbitRadius = 110;
                orbitSpeed = 0.45;
                particleSize = 3;
                randomness = 0.25;
                verticalSpread = 45;
            }
        }
        // MacOS optimization
        else if (deviceInfo.isMac) {
            if (perfProfile.isHighEnd) {
                particleCount = 120;
                targetFPS = 60;
                orbitRadius = 150;
                orbitSpeed = 0.6;
                particleSize = 4;
                randomness = 0.35;
                verticalSpread = 60;
            } else {
                particleCount = 80;
                targetFPS = 50;
                orbitRadius = 130;
                orbitSpeed = 0.5;
                particleSize = 3.5;
                randomness = 0.3;
                verticalSpread = 50;
            }
        }
        // Desktop/Laptop optimization
        else {
            if (perfProfile.isHighEnd) {
                particleCount = 100;
                targetFPS = 60;
                orbitRadius = 140;
                orbitSpeed = 0.55;
                particleSize = 4;
                randomness = 0.35;
                verticalSpread = 55;
            } else {
                particleCount = 70;
                targetFPS = 50;
                orbitRadius = 120;
                orbitSpeed = 0.5;
                particleSize = 3;
                randomness = 0.3;
                verticalSpread = 50;
            }
        }

        // Respect prefers-reduced-motion
        if (deviceInfo.prefersReducedMotion) {
            orbitSpeed *= 0.3;
            particleCount = Math.floor(particleCount * 0.5);
            randomness *= 0.5;
        }

        return {
            particleCount,
            targetFPS,
            orbitRadius,
            orbitSpeed,
            particleSize,
            randomness,
            verticalSpread
        };
    }

    function initFlyAroundText(elementId, options) {
        const targetElement = document.getElementById(elementId);
        if (!targetElement) return null;

        const cfg = getFlyAroundConfig();

        // Create canvas overlay positioned over the text element
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Style the canvas to overlay the text element
        canvas.style.position = 'absolute';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '1';

        // Ensure target element has relative positioning
        const computedStyle = window.getComputedStyle(targetElement);
        if (computedStyle.position === 'static') {
            targetElement.style.position = 'relative';
        }
        targetElement.appendChild(canvas);

        let width, height, centerX, centerY;
        let animationId;
        let lastTime = 0;
        const minFrameInterval = 1000 / cfg.targetFPS;

        // Particle data
        const particles = [];

        function resize() {
            const rect = targetElement.getBoundingClientRect();
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            width = rect.width;
            height = rect.height;
            canvas.width = width * dpr;
            canvas.height = height * dpr;
            ctx.scale(dpr, dpr);
            centerX = width / 2;
            centerY = height / 2;
        }

        function initParticles() {
            particles.length = 0;
            for (let i = 0; i < cfg.particleCount; i++) {
                particles.push({
                    angle: (Math.PI * 2 * i) / cfg.particleCount + Math.random() * 0.5,
                    radius: cfg.orbitRadius * (0.7 + Math.random() * 0.6),
                    speed: cfg.orbitSpeed * (0.5 + Math.random() * 1),
                    yOffset: (Math.random() - 0.5) * cfg.verticalSpread,
                    size: cfg.particleSize * (0.6 + Math.random() * 0.8),
                    color: Math.random() > 0.5 ? '#1f5d2b' : '#55A147',
                    opacity: 0.3 + Math.random() * 0.5,
                    wobble: Math.random() * Math.PI * 2,
                    wobbleSpeed: 0.02 + Math.random() * 0.03
                });
            }
        }

        resize();
        initParticles();
        window.addEventListener('resize', () => { resize(); initParticles(); });

        // Color utilities
        function lightenColor(color, percent) {
            const num = parseInt(color.replace('#', ''), 16);
            const amt = Math.round(2.55 * percent);
            const R = Math.min(255, (num >> 16) + amt);
            const G = Math.min(255, ((num >> 8) & 0x00FF) + amt);
            const B = Math.min(255, (num & 0x0000FF) + amt);
            return '#' + (0x1000000 + R * 0x10000 + G * 0x100 + B).toString(16).slice(1);
        }

        function drawParticle(x, y, radius, color, opacity) {
            // Shadow
            ctx.beginPath();
            ctx.arc(x + 1, y + 1, radius, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(31, 93, 43, 0.1)';
            ctx.fill();

            // Main sphere with gradient
            const gradient = ctx.createRadialGradient(
                x - radius * 0.3, y - radius * 0.3, 0,
                x, y, radius
            );
            gradient.addColorStop(0, lightenColor(color, 50));
            gradient.addColorStop(0.6, color);
            gradient.addColorStop(1, color);

            ctx.beginPath();
            ctx.arc(x, y, radius, 0, Math.PI * 2);
            ctx.fillStyle = gradient;
            ctx.globalAlpha = opacity;
            ctx.fill();
            ctx.globalAlpha = 1;

            // Highlight
            ctx.beginPath();
            ctx.arc(x - radius * 0.25, y - radius * 0.25, radius * 0.25, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.fill();
        }

        function animate(currentTime) {
            animationId = requestAnimationFrame(animate);

            const elapsed = currentTime - lastTime;
            if (elapsed < minFrameInterval) return;
            lastTime = currentTime - (elapsed % minFrameInterval);

            ctx.clearRect(0, 0, width, height);

            const time = currentTime / 1000;

            // Sort particles by Y for depth effect (back to front)
            const sortedParticles = particles.map((p, index) => {
                const wobble = Math.sin(time * p.wobbleSpeed + p.wobble) * cfg.randomness;
                const currentAngle = p.angle + time * p.speed + wobble;
                const currentRadius = p.radius + Math.sin(time * 0.5 + p.wobble) * 10 * cfg.randomness;

                const x = centerX + Math.cos(currentAngle) * currentRadius;
                const y = centerY + p.yOffset + Math.sin(time * 0.3 + p.wobble) * 5;

                // Calculate depth based on Y position and angle
                const depth = (Math.sin(currentAngle) + 1) / 2;

                return { x, y, depth, particle: p, index };
            }).sort((a, b) => a.depth - b.depth);

            // Draw particles
            sortedParticles.forEach(({ x, y, depth, particle }) => {
                // Fade particles in back, bright in front
                const depthOpacity = 0.2 + depth * 0.8;
                const finalOpacity = particle.opacity * depthOpacity;

                // Scale size slightly based on depth
                const depthScale = 0.7 + depth * 0.3;
                const finalSize = particle.size * depthScale;

                drawParticle(x, y, finalSize, particle.color, finalOpacity);
            });

            // Optional: Draw faint orbit lines for some particles
            ctx.globalAlpha = 0.05;
            ctx.strokeStyle = '#1f5d2b';
            ctx.lineWidth = 1;

            for (let i = 0; i < Math.min(3, particles.length); i++) {
                const p = particles[i];
                ctx.beginPath();
                ctx.ellipse(centerX, centerY + p.yOffset, p.radius, p.radius * 0.3, 0, 0, Math.PI * 2);
                ctx.stroke();
            }
            ctx.globalAlpha = 1;
        }

        animationId = requestAnimationFrame(animate);

        return () => {
            if (animationId) cancelAnimationFrame(animationId);
            window.removeEventListener('resize', resize);
            if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
        };
    }

    function getHelixConfig() {
        let pairCount = 160;
        let targetFPS = 30;
        let skipRungDraw = 1;
        let animationSpeed = 0.01;
        let sizeBase = 4.2;
        let sizeAmp = 3.8;

        // Android optimization
        if (deviceInfo.isAndroid) {
            if (perfProfile.isLowEnd) {
                pairCount = 40;
                targetFPS = 18;
                skipRungDraw = 2;
                animationSpeed = 0.005;
                sizeBase = 1.8;
                sizeAmp = 1.2;
            } else if (perfProfile.isMidRange) {
                pairCount = 80;
                targetFPS = 24;
                skipRungDraw = 1;
                animationSpeed = 0.008;
                sizeBase = 2.6;
                sizeAmp = 1.6;
            }
        }
        // iOS optimization
        else if (deviceInfo.isIOS) {
            if (perfProfile.isLowEnd) {
                pairCount = 60;
                targetFPS = 20;
                skipRungDraw = 2;
                animationSpeed = 0.007;
                sizeBase = 2.2;
                sizeAmp = 1.4;
            } else if (perfProfile.isMidRange) {
                pairCount = 100;
                targetFPS = 28;
                skipRungDraw = 1;
                animationSpeed = 0.009;
                sizeBase = 2.6;
                sizeAmp = 1.8;
            }
        }
        // MacOS optimization
        else if (deviceInfo.isMac) {
            if (perfProfile.isHighEnd) {
                pairCount = 200;
                targetFPS = 60;
                skipRungDraw = 1;
                animationSpeed = 0.012;
                sizeBase = 5;
                sizeAmp = 4.5;
            } else {
                pairCount = 160;
                targetFPS = 50;
                skipRungDraw = 1;
                animationSpeed = 0.01;
                sizeBase = 4.2;
                sizeAmp = 3.8;
            }
        }
        // Desktop/Laptop optimization
        else {
            if (perfProfile.isHighEnd) {
                pairCount = 200;
                targetFPS = 60;
                skipRungDraw = 1;
                animationSpeed = 0.012;
                sizeBase = 5;
                sizeAmp = 4.5;
            } else {
                pairCount = 160;
                targetFPS = 50;
                skipRungDraw = 1;
                animationSpeed = 0.01;
                sizeBase = 4.2;
                sizeAmp = 3.8;
            }
        }

        // Respect prefers-reduced-motion
        if (deviceInfo.prefersReducedMotion) {
            animationSpeed *= 0.3;
            pairCount = Math.floor(pairCount * 0.3);
            skipRungDraw = 2;
        }

        return {
            pairCount,
            targetFPS,
            skipRungDraw,
            animationSpeed,
            sizeBase,
            sizeAmp
        };
    }

    function initHelix(containerId, options) {
        if (typeof particlesJS !== "function") return null;

        const helixCfg = getHelixConfig();

        particlesJS(containerId, {
            particles: {
                number: { value: helixCfg.pairCount * 2, density: { enable: true, value_area: 800 } },
                color: { value: ["#006837", "#acc638"] },
                shape: { type: "circle" },
                opacity: { value: 0.9, random: true },
                size: { value: 2.2, random: false },
                line_linked: { enable: false },
                move: { enable: false }
            },
            interactivity: { detect_on: "canvas", events: { onhover: { enable: false }, onclick: { enable: false }, resize: true } },
            retina_detect: !deviceInfo.isMobile
        });

        function startWhenReady() {
            // find the pJS instance for this container id
            const inst = (window.pJSDom || []).find(d => d && d.pJS && d.pJS.canvas && d.pJS.canvas.el && d.pJS.canvas.el.parentElement && d.pJS.canvas.el.parentElement.id === containerId);
            if (!inst) {
                requestAnimationFrame(startWhenReady);
                return;
            }
            const p = inst.pJS;
            const canvas = p.canvas.el;
            const ctx = canvas.getContext("2d");

            p.particles.move.enable = false;
            if (p.particles.line_linked) p.particles.line_linked.enable = false;

            let lastW = canvas.width;
            let lastH = canvas.height;
            let lastPairs = -1;
            let rafId = 0;
            let running = true;
            let lastDrawMs = 0;
            const targetFPS = helixCfg.targetFPS;
            const minFrameInterval = 1000 / targetFPS;

            const rungColor = "#7fb343";
            const frontColor = "#acc638";
            const backColor = "#006837";

            // Determine horizontal placement of the helix: 'left', 'right', or 'center' (default)
            const containerEl = document.getElementById(containerId);
            const dataPos = containerEl ? (containerEl.getAttribute('data-helix-position') || containerEl.getAttribute('data-position')) : null;
            const position = ((options && options.position) || dataPos || 'center').toLowerCase();

            function getParams() {
                const w = canvas.width;
                const h = canvas.height;

                // Adaptive amplitude based on device
                let amplitude;
                if (deviceInfo.isAndroid && perfProfile.isLowEnd) {
                    amplitude = Math.max(12, Math.min(0.08 * w, 40));
                } else if (deviceInfo.isMobile) {
                    amplitude = Math.max(22, Math.min(0.12 * w, 80));
                } else {
                    amplitude = Math.max(40, Math.min(0.22 * w, 110));
                }

                // Choose center X based on requested position
                let centerX;
                const sideTarget = Math.max(amplitude + 100, Math.round(w * 0.20));
                const rightTarget = Math.min(w - amplitude - 100, Math.round(w * 0.80));
                switch (position) {
                    case 'left':
                        centerX = sideTarget;
                        break;
                    case 'right':
                        centerX = rightTarget;
                        break;
                    default:
                        centerX = Math.round(w / 2);
                        break;
                }

                // Adaptive vertical step based on device
                let verticalStep;
                if (deviceInfo.isAndroid && perfProfile.isLowEnd) {
                    verticalStep = Math.max(20, Math.min(30, Math.round(h / 20)));
                } else if (deviceInfo.isMobile) {
                    verticalStep = Math.max(16, Math.min(24, Math.round(h / 26)));
                } else {
                    verticalStep = Math.max(16, Math.min(26, Math.round(h / 24)));
                }

                return { w, h, centerX, amplitude, verticalStep };
            }

            function ensureParticleCount(targetPairs) {
                const need = targetPairs * 2;
                const have = p.particles.array.length;
                if (have < need && p.fn.modes && typeof p.fn.modes.pushParticles === "function") {
                    p.fn.modes.pushParticles(need - have, null);
                } else if (have > need) {
                    // Trim extra particles to reduce per-frame iteration cost on mobile
                    p.particles.array.length = need;
                }
            }

            function animate() {
                if (!running || !canvas.isConnected) return;

                const now = performance.now();
                if (now - lastDrawMs < minFrameInterval) {
                    rafId = requestAnimationFrame(animate);
                    return;
                }
                lastDrawMs = now;
                const time = now / 1000;

                const { w, h, centerX, amplitude, verticalStep } = getParams();
                const particles = p.particles.array;

                // recompute pairs and only adjust count if changed
                let pairs = Math.ceil(h / verticalStep) + 2;
                if (pairs !== lastPairs || canvas.width !== lastW || canvas.height !== lastH) {
                    ensureParticleCount(pairs);
                    lastPairs = pairs;
                    lastW = canvas.width;
                    lastH = canvas.height;
                }
                const maxPairs = Math.floor(particles.length / 2);
                pairs = Math.min(pairs, maxPairs);

                // Animation parameters - optimized per device
                const backFade = deviceInfo.isAndroid && perfProfile.isLowEnd ? 0.4 : (deviceInfo.isMobile ? 0.3 : 0.15);
                const sizeBase = helixCfg.sizeBase;
                const sizeAmp = helixCfg.sizeAmp;
                const twistSpeed = helixCfg.animationSpeed;
                const omegaY = 0.012;
                const scrollSpeed = deviceInfo.isAndroid && perfProfile.isLowEnd ? 5 : (deviceInfo.isMobile ? 8 : 10);
                const phaseT = time * twistSpeed;
                const yOffset = (time * scrollSpeed) % verticalStep;

                // Compute snake-like bend parameters once per frame
                const desiredBend = deviceInfo.isAndroid && perfProfile.isLowEnd ?
                    Math.min(0.04 * w, 12) :
                    (deviceInfo.isMobile ? Math.min(0.06 * w, 26) : Math.min(0.18 * w, 100));
                const margin = 12;
                const maxSafeBend = Math.max(0, (w / 2) - amplitude - margin);
                const bendAmp = Math.min(desiredBend, maxSafeBend);
                const bendFreq1 = 0.004;
                const bendFreq2 = 0.0073;
                const bend2Factor = deviceInfo.isMobile ? 0.45 : 0.6;
                const bendPhase = time * 0.25;

                for (let i = 0; i < pairs; i++) {
                    const y = (i * verticalStep + yOffset) % h;
                    const s = Math.sin(omegaY * y + phaseT);
                    const c = Math.cos(omegaY * y + phaseT);

                    // Centerline wanders like a snake across the canvas
                    const centerline = centerX
                        + bendAmp * Math.sin(bendFreq1 * y + bendPhase)
                        + bendAmp * bend2Factor * Math.sin(bendFreq2 * y - bendPhase * 0.7);

                    const offset = amplitude * s;
                    const leftX = centerline - offset;
                    const rightX = centerline + offset;

                    const left = particles[i * 2];
                    const right = particles[i * 2 + 1];
                    if (!left || !right) break;

                    left.x = leftX; left.y = y;
                    right.x = rightX; right.y = y;

                    const depth = (c + 1) / 2;
                    const opacity = backFade + depth * (1 - backFade);
                    const size = sizeBase + sizeAmp * depth;

                    left.opacity = opacity;
                    right.opacity = opacity;
                    left.radius = size;
                    right.radius = size;

                    left.color.value = backColor;
                    right.color.value = frontColor;
                }

                // Clear any unused tail entries (if any remain)
                for (let j = pairs * 2; j < particles.length; j++) {
                    particles[j].opacity = 0;
                    particles[j].radius = 0;
                }

                p.fn.canvasClear();
                p.fn.particlesDraw();

                ctx.save();
                ctx.globalAlpha = 0.55;
                ctx.lineWidth = deviceInfo.isAndroid && perfProfile.isLowEnd ? 0.6 : (deviceInfo.isMobile ? 0.9 : 1.2);
                ctx.strokeStyle = rungColor;
                const rungStep = helixCfg.skipRungDraw;
                for (let i = 0; i < pairs; i += rungStep) {
                    const a = p.particles.array[i * 2];
                    const b = p.particles.array[i * 2 + 1];
                    if (!a || !b) continue;
                    const c = Math.cos(omegaY * a.y + phaseT);
                    const depth = (c + 1) / 2;
                    ctx.globalAlpha = 0.35 + depth * 0.5;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
                ctx.restore();

                rafId = requestAnimationFrame(animate);
            }

            rafId = requestAnimationFrame(animate);

            return () => {
                running = false;
                if (rafId) cancelAnimationFrame(rafId);
            };
        }
        return startWhenReady();
    }

    function boot() {
        const initializers = {
            network: initNetwork,
            helix: initHelix,
            flyAroundText: initFlyAroundText
        };

        // Backward-compat IDs
        if (document.getElementById("particles-js-network")) {
            initializers.network("particles-js-network");
        }
        if (document.getElementById("particles-js-helix")) {
            initializers.helix("particles-js-helix");
        }
        if (document.getElementById("particles-js-flyAroundText")) {
            initializers.flyAroundText("particles-js-flyAroundText");
        }

        // Data-attribute driven init: <div id="..." data-particles-type="network|helix"></div>
        const nodes = document.querySelectorAll('[data-particles-type]');
        nodes.forEach(node => {
            const type = (node.getAttribute('data-particles-type') || '').toLowerCase();
            const id = node.id;
            if (!id || !(type in initializers)) return;
            // Skip if already initialized via back-compat step
            if ((type === 'network' && id === 'particles-js-network') || (type === 'helix' && id === 'particles-js-helix') || (type === 'flyAroundText' && id === 'particles-js-flyAroundText')) return;
            initializers[type](id);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
