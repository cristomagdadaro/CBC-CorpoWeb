// Particles init (optimized) supporting multiple types: "network" and "helix".
// Backward-compatible with #particles-js-network and #particles-js-helix containers.
(function () {
    // Device/adaptive helpers
    const isMobile = (() => {
        return false;
        /*try {
            // Modern preferred check
            const mq = window.matchMedia?.('(pointer: coarse) and (max-width: 1024px)');
            if (mq?.matches) return true;

            // UA fallback (legacy support)
            const ua = navigator.userAgent?.toLowerCase() || '';

            return /mobile|android|iphone|ipad|ipod|tablet|touch/i.test(ua)
        } catch {
            return false;
        }*/
    })();


    function initNetwork(containerId, overrides) {
        if (typeof particlesJS !== "function") return null;
        const cfg = {
            particles: {
                number: { value: isMobile ? 80 : 200, density: { enable: true, value_area: 800 } },
                color: { value: "#235F2A" },
                shape: { type: "circle", stroke: { width: 0, color: "#000000" }, polygon: { nb_sides: 5 } },
                opacity: { value: 0.5, random: false },
                size: { value: isMobile ? 4 : 5, random: true },
                line_linked: { enable: !isMobile, distance: 150, color: "#acc638", opacity: 0.35, width: 1 },
                move: { enable: true, speed: isMobile ? 0.6 : 1, direction: "none", random: true, straight: false, out_mode: "out", bounce: false }
            },
            interactivity: {
                detect_on: "canvas",
                events: { onhover: { enable: false, mode: "repulse" }, onclick: { enable: true, mode: "push" }, resize: true },
                modes: {
                    grab: { distance: 400, line_linked: { opacity: 1 } },
                    bubble: { distance: 400, size: 40, duration: 2, opacity: 8, speed: 3 },
                    repulse: { distance: 200, duration: 0.4 },
                    push: { particles_nb: 1 },
                    remove: { particles_nb: 2 }
                }
            },
            retina_detect: !isMobile
        };
        // Apply shallow overrides if provided
        if (overrides && typeof overrides === "object") {
            Object.assign(cfg.particles, overrides.particles || {});
            Object.assign(cfg.interactivity || {}, overrides.interactivity || {});
        }
        particlesJS(containerId, cfg);
        return function dispose() { /* particles.js does not expose dispose per instance reliably */ };
    }

    function initHelix(containerId, options) {
        if (typeof particlesJS !== "function") return null;
        particlesJS(containerId, {
            particles: {
                number: { value: isMobile ? 160 : 160, density: { enable: true, value_area: 800 } },
                color: { value: ["#006837", "#acc638"] },
                shape: { type: "circle" },
                opacity: { value: 0.9, random: true },
                size: { value: isMobile ? 2.2 : 2.2, random: false },
                line_linked: { enable: false },
                move: { enable: false }
            },
            interactivity: { detect_on: "canvas", events: { onhover: { enable: false }, onclick: { enable: false }, resize: true } },
            retina_detect: !isMobile
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
            const targetFPS = isMobile ? 23 : 30;
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
                const amplitude = isMobile
                    ? Math.max(22, Math.min(0.12 * w, 80))
                    : Math.max(40, Math.min(0.22 * w, 110));
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
                const verticalStep = isMobile
                    ? Math.max(16, Math.min(24, Math.round(h / 26)))
                    : Math.max(16, Math.min(26, Math.round(h / 24)));
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

                // Keep visual tweaks from previous tasks (tuned for mobile coherence)
                const backFade = isMobile ? 0.3 : 0.15;
                const sizeBase = isMobile ? 2.6 : 4.2;
                const sizeAmp = isMobile ? 1.6 : 3.8;
                const twistSpeed = 0.01; // 50% slower already applied previously
                const omegaY = 0.012;
                const scrollSpeed = isMobile ? 8 : 10; // slower on mobile for stability
                const phaseT = time * twistSpeed;
                const yOffset = (time * scrollSpeed) % verticalStep;

                // Compute snake-like bend parameters once per frame
                const desiredBend = isMobile ? Math.min(0.06 * w, 26) : Math.min(0.18 * w, 100);
                const margin = 12;
                const maxSafeBend = Math.max(0, (w / 2) - amplitude - margin);
                const bendAmp = Math.min(desiredBend, maxSafeBend);
                const bendFreq1 = 0.004; // low-frequency lateral drift
                const bendFreq2 = 0.0073; // second incommensurate freq for pseudo-randomness
                const bend2Factor = isMobile ? 0.45 : 0.6;
                const bendPhase = time * 0.25; // slow global phase for wandering

                for (let i = 0; i < pairs; i++) {
                    const y = (i * verticalStep + yOffset) % h;
                    const s = Math.sin(omegaY * y + phaseT);
                    const c = Math.cos(omegaY * y + phaseT);

                    // Centerline wanders like a snake across the canvas (reduced on mobile)
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
                ctx.lineWidth = isMobile ? 0.9 : 1.2;
                ctx.strokeStyle = rungColor;
                const rungStep = 1; // draw every rung for tighter look on mobile
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
            helix: initHelix
        };

        // Backward-compat IDs
        if (document.getElementById("particles-js-network")) {
            initializers.network("particles-js-network");
        }
        if (document.getElementById("particles-js-helix")) {
            initializers.helix("particles-js-helix");
        }

        // Data-attribute driven init: <div id="..." data-particles-type="network|helix"></div>
        const nodes = document.querySelectorAll('[data-particles-type]');
        nodes.forEach(node => {
            const type = (node.getAttribute('data-particles-type') || '').toLowerCase();
            const id = node.id;
            if (!id || !(type in initializers)) return;
            // Skip if already initialized via back-compat step
            if ((type === 'network' && id === 'particles-js-network') || (type === 'helix' && id === 'particles-js-helix')) return;
            initializers[type](id);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
