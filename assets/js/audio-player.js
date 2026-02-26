// Lecteur Audio Amélioré
class EnhancedAudioPlayer {
    constructor(container) {
        this.container = container;
        this.audio = container.querySelector('audio');
        this.playBtn = container.querySelector('.btn-play');
        this.progressBar = container.querySelector('.progress-bar');
        this.progressFill = container.querySelector('.progress-fill');
        this.currentTimeEl = container.querySelector('.current-time');
        this.durationEl = container.querySelector('.duration');
        this.volumeSlider = container.querySelector('.volume-slider');
        this.speedBtns = container.querySelectorAll('.btn-speed');
        
        this.isPlaying = false;
        this.init();
    }

    init() {
        // Play/Pause
        this.playBtn?.addEventListener('click', () => this.togglePlay());
        
        // Progress bar click
        this.progressBar?.addEventListener('click', (e) => this.seek(e));
        
        // Audio events
        this.audio?.addEventListener('timeupdate', () => this.updateProgress());
        this.audio?.addEventListener('loadedmetadata', () => this.updateDuration());
        this.audio?.addEventListener('ended', () => this.onEnded());
        
        // Volume
        this.volumeSlider?.addEventListener('input', (e) => {
            this.audio.volume = e.target.value;
        });
        
        // Speed controls
        this.speedBtns?.forEach(btn => {
            btn.addEventListener('click', () => this.setSpeed(btn));
        });
    }

    togglePlay() {
        if (this.isPlaying) {
            this.audio.pause();
            this.playBtn.textContent = '▶';
        } else {
            this.audio.play();
            this.playBtn.textContent = '⏸';
        }
        this.isPlaying = !this.isPlaying;
    }

    seek(e) {
        const rect = this.progressBar.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        this.audio.currentTime = percent * this.audio.duration;
    }

    updateProgress() {
        const percent = (this.audio.currentTime / this.audio.duration) * 100;
        this.progressFill.style.width = percent + '%';
        this.currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
    }

    updateDuration() {
        this.durationEl.textContent = this.formatTime(this.audio.duration);
    }

    onEnded() {
        this.isPlaying = false;
        this.playBtn.textContent = '▶';
        this.progressFill.style.width = '0%';
    }

    setSpeed(btn) {
        const speed = parseFloat(btn.dataset.speed);
        this.audio.playbackRate = speed;
        this.speedBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// Mini player pour les listes
class MiniAudioPlayer {
    constructor(container) {
        this.container = container;
        this.audio = container.querySelector('audio');
        this.playBtn = container.querySelector('.btn-play-mini');
        this.progressFill = container.querySelector('.mini-progress-fill');
        this.timeEl = container.querySelector('.mini-time');
        
        this.isPlaying = false;
        this.init();
    }

    init() {
        this.playBtn?.addEventListener('click', () => this.togglePlay());
        this.audio?.addEventListener('timeupdate', () => this.updateProgress());
        this.audio?.addEventListener('loadedmetadata', () => this.updateTime());
        this.audio?.addEventListener('ended', () => this.onEnded());
    }

    togglePlay() {
        if (this.isPlaying) {
            this.audio.pause();
            this.playBtn.textContent = '▶';
        } else {
            // Pause all other players
            document.querySelectorAll('.audio-player-mini audio').forEach(a => {
                if (a !== this.audio) {
                    a.pause();
                    a.closest('.audio-player-mini').querySelector('.btn-play-mini').textContent = '▶';
                }
            });
            this.audio.play();
            this.playBtn.textContent = '⏸';
        }
        this.isPlaying = !this.isPlaying;
    }

    updateProgress() {
        const percent = (this.audio.currentTime / this.audio.duration) * 100;
        this.progressFill.style.width = percent + '%';
        this.updateTime();
    }

    updateTime() {
        const current = this.formatTime(this.audio.currentTime);
        const duration = this.formatTime(this.audio.duration);
        this.timeEl.textContent = `${current} / ${duration}`;
    }

    onEnded() {
        this.isPlaying = false;
        this.playBtn.textContent = '▶';
        this.progressFill.style.width = '0%';
    }

    formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.audio-player-enhanced').forEach(container => {
        new EnhancedAudioPlayer(container);
    });
    
    document.querySelectorAll('.audio-player-mini').forEach(container => {
        new MiniAudioPlayer(container);
    });
});

// Track downloads
function trackDownload(projectId, fileType) {
    fetch(`/api/download.php?id=${projectId}&type=${fileType}`)
        .then(r => r.json())
        .then(data => {
            if (data.downloads !== undefined) {
                const countEl = document.querySelector(`[data-project-id="${projectId}"] .download-count`);
                if (countEl) countEl.textContent = data.downloads;
            }
        })
        .catch(console.error);
}
