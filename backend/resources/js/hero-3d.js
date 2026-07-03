// Hero 3D Canvas Animation
class Hero3DCanvas {
    constructor(canvasId = 'canvas3d') {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.width = this.canvas.width = window.innerWidth;
        this.height = this.canvas.height = window.innerHeight;

        this.particles = [];
        this.waves = [];
        this.mouse = { x: this.width / 2, y: this.height / 2 };
        this.time = 0;

        this.init();
        this.animate();

        window.addEventListener('resize', () => this.resize());
        window.addEventListener('mousemove', (e) => this.onMouseMove(e));
    }

    init() {
        // Initialize particles
        this.createParticles();
        // Initialize waves
        this.createWaves();
    }

    createParticles() {
        const count = Math.min(150, Math.floor((this.width * this.height) / 20000));
        for (let i = 0; i < count; i++) {
            this.particles.push({
                x: Math.random() * this.width,
                y: Math.random() * this.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                size: Math.random() * 1.5,
                opacity: Math.random() * 0.5 + 0.2,
                color: this.getRandomGlowColor(),
                pulse: Math.random() * Math.PI * 2
            });
        }
    }

    createWaves() {
        this.waves = [
            { amplitude: 80, frequency: 0.005, phase: 0, y: this.height * 0.4, color: '#64c8ff', thickness: 2 },
            { amplitude: 60, frequency: 0.008, phase: Math.PI / 4, y: this.height * 0.5, color: '#ff6b9d', thickness: 1.5 },
            { amplitude: 40, frequency: 0.003, phase: Math.PI / 2, y: this.height * 0.6, color: '#00ffff', thickness: 1 }
        ];
    }

    getRandomGlowColor() {
        const colors = ['#64c8ff', '#ff6b9d', '#00ffff', '#00ff88'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    onMouseMove(e) {
        this.mouse.x = e.clientX;
        this.mouse.y = e.clientY;
    }

    resize() {
        this.width = this.canvas.width = window.innerWidth;
        this.height = this.canvas.height = window.innerHeight;
    }

    drawParticles() {
        this.particles.forEach(particle => {
            // Update position
            particle.x += particle.vx;
            particle.y += particle.vy;

            // Wrap around edges
            if (particle.x < 0) particle.x = this.width;
            if (particle.x > this.width) particle.x = 0;
            if (particle.y < 0) particle.y = this.height;
            if (particle.y > this.height) particle.y = 0;

            // Pulse effect
            particle.pulse += 0.02;
            const pulseOpacity = particle.opacity + Math.sin(particle.pulse) * 0.2;

            // Distance to mouse
            const dx = particle.x - this.mouse.x;
            const dy = particle.y - this.mouse.y;
            const distance = Math.sqrt(dx * dx + dy * dy);

            // Mouse interaction
            if (distance < 150) {
                const angle = Math.atan2(dy, dx);
                particle.vx += Math.cos(angle) * 0.05;
                particle.vy += Math.sin(angle) * 0.05;
            }

            // Limit velocity
            const maxVelocity = 1.5;
            const velocity = Math.sqrt(particle.vx * particle.vx + particle.vy * particle.vy);
            if (velocity > maxVelocity) {
                particle.vx = (particle.vx / velocity) * maxVelocity;
                particle.vy = (particle.vy / velocity) * maxVelocity;
            }

            // Draw particle
            this.ctx.save();
            this.ctx.globalAlpha = Math.max(0, pulseOpacity);
            this.ctx.fillStyle = particle.color;
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            this.ctx.fill();

            // Glow effect
            this.ctx.shadowBlur = 10;
            this.ctx.shadowColor = particle.color;
            this.ctx.fillStyle = particle.color;
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.size * 0.5, 0, Math.PI * 2);
            this.ctx.fill();
            this.ctx.restore();
        });

        // Draw connections
        this.drawConnections();
    }

    drawConnections() {
        const maxDistance = 150;
        this.ctx.strokeStyle = 'rgba(100, 200, 255, 0.1)';
        this.ctx.lineWidth = 0.5;

        for (let i = 0; i < this.particles.length; i++) {
            for (let j = i + 1; j < this.particles.length; j++) {
                const dx = this.particles[i].x - this.particles[j].x;
                const dy = this.particles[i].y - this.particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < maxDistance) {
                    const opacity = 1 - (distance / maxDistance);
                    this.ctx.globalAlpha = opacity * 0.2;
                    this.ctx.beginPath();
                    this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                    this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                    this.ctx.stroke();
                    this.ctx.globalAlpha = 1;
                }
            }
        }
    }

    drawWaves() {
        this.waves.forEach((wave, waveIndex) => {
            this.ctx.strokeStyle = wave.color;
            this.ctx.lineWidth = wave.thickness;
            this.ctx.globalAlpha = 0.4 - waveIndex * 0.1;
            this.ctx.beginPath();

            for (let x = 0; x < this.width; x += 5) {
                const y = wave.y + 
                    Math.sin(x * wave.frequency + this.time * 0.02 + wave.phase) * wave.amplitude +
                    Math.sin(x * wave.frequency * 0.5) * (wave.amplitude * 0.3);

                if (x === 0) {
                    this.ctx.moveTo(x, y);
                } else {
                    this.ctx.lineTo(x, y);
                }
            }

            this.ctx.stroke();

            // Fill under wave
            this.ctx.lineTo(this.width, this.height);
            this.ctx.lineTo(0, this.height);
            this.ctx.closePath();
            this.ctx.globalAlpha = 0.05 - waveIndex * 0.01;
            this.ctx.fillStyle = wave.color;
            this.ctx.fill();
        });

        this.ctx.globalAlpha = 1;
    }

    drawBackgroundGradient() {
        // Create gradient background
        const gradient = this.ctx.createLinearGradient(0, 0, this.width, this.height);
        gradient.addColorStop(0, '#0a0e27');
        gradient.addColorStop(0.5, '#1a2550');
        gradient.addColorStop(1, '#0f1535');

        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, this.width, this.height);

        // Add radial glow from mouse
        const radialGradient = this.ctx.createRadialGradient(
            this.mouse.x, this.mouse.y, 0,
            this.mouse.x, this.mouse.y, 400
        );
        radialGradient.addColorStop(0, 'rgba(100, 200, 255, 0.1)');
        radialGradient.addColorStop(1, 'rgba(100, 200, 255, 0)');

        this.ctx.fillStyle = radialGradient;
        this.ctx.fillRect(0, 0, this.width, this.height);
    }

    animate = () => {
        this.time++;

        this.drawBackgroundGradient();
        this.drawWaves();
        this.drawParticles();

        requestAnimationFrame(this.animate);
    }
}

// Particle Effects System
class ParticleEffects {
    constructor(containerId = 'particles-container') {
        this.container = document.getElementById(containerId);
        if (!this.container) return;

        this.particles = [];
        this.init();
    }

    init() {
        // Create initial floating particles
        this.createFloatingParticles();

        // Add event listeners for interactions
        document.addEventListener('click', (e) => this.onClickEffect(e));
    }

    createFloatingParticles() {
        const count = 20;
        for (let i = 0; i < count; i++) {
            this.addFloatingParticle(
                Math.random() * window.innerWidth,
                Math.random() * window.innerHeight
            );
        }
    }

    addFloatingParticle(x, y) {
        const particle = document.createElement('div');
        particle.className = 'particle particle-dot';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';

        const duration = Math.random() * 10 + 15;
        const angle = Math.random() * Math.PI * 2;
        const distance = Math.random() * 200 + 100;

        const endX = x + Math.cos(angle) * distance;
        const endY = y + Math.sin(angle) * distance;

        particle.style.animation = `
            floatParticle ${duration}s ease-out forwards,
            fadeOutParticle ${duration}s ease-out forwards
        `;

        particle.style.setProperty('--endX', endX + 'px');
        particle.style.setProperty('--endY', endY + 'px');

        this.container.appendChild(particle);

        setTimeout(() => {
            particle.remove();
            // Replace with new particle
            this.addFloatingParticle(
                Math.random() * window.innerWidth,
                Math.random() * window.innerHeight
            );
        }, duration * 1000);
    }

    onClickEffect(e) {
        // Create burst effect on click
        for (let i = 0; i < 15; i++) {
            this.addBurstParticle(e.clientX, e.clientY);
        }
    }

    addBurstParticle(x, y) {
        const particle = document.createElement('div');
        particle.className = 'particle particle-dot';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';

        const angle = (Math.PI * 2 / 15) * Math.random();
        const velocity = Math.random() * 5 + 3;
        const distance = velocity * 30;

        const endX = x + Math.cos(angle) * distance;
        const endY = y + Math.sin(angle) * distance;

        particle.style.animation = `
            burstParticle 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards,
            fadeOutParticle 1.5s ease-out forwards
        `;

        particle.style.setProperty('--endX', endX + 'px');
        particle.style.setProperty('--endY', endY + 'px');

        this.container.appendChild(particle);

        setTimeout(() => particle.remove(), 1500);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize 3D Canvas
    new Hero3DCanvas('canvas3d');

    // Initialize Particle Effects
    new ParticleEffects('particles-container');

    // Button interactions
    const followBtn = document.getElementById('follow-btn');
    const subscribeBtn = document.getElementById('subscribe-btn');

    if (followBtn) {
        followBtn.addEventListener('click', () => {
            followBtn.textContent = 'FOLLOWING ✓';
            followBtn.style.borderColor = '#64c8ff';
        });
    }

    if (subscribeBtn) {
        subscribeBtn.addEventListener('click', () => {
            subscribeBtn.textContent = 'SUBSCRIBED ✓';
            subscribeBtn.style.borderColor = '#64c8ff';
        });
    }
});

// Add animations to stylesheet dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes floatParticle {
        to {
            transform: translate(calc(var(--endX) - 50%), calc(var(--endY) - 50%));
        }
    }

    @keyframes fadeOutParticle {
        to {
            opacity: 0;
        }
    }

    @keyframes burstParticle {
        to {
            transform: translate(calc(var(--endX) - 50%), calc(var(--endY) - 50%));
        }
    }
`;
document.head.appendChild(style);
