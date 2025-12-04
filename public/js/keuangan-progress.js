/**
 * Real-time Keuangan Progress Updater
 * Auto-refresh progress pembayaran tanpa reload halaman
 */

class KeuanganProgressUpdater {
  constructor() {
    this.keuanganIds = [];
    this.updateInterval = 30000; // Update setiap 30 detik
    this.intervalId = null;
  }

  /**
   * Initialize updater dengan keuangan_id yang perlu dimonitor
   */
  init(keuanganIds) {
    this.keuanganIds = Array.isArray(keuanganIds) ? keuanganIds : [keuanganIds];
    
    // Update pertama kali
    this.updateAllProgress();
    
    // Auto-update setiap interval
    this.intervalId = setInterval(() => {
      this.updateAllProgress();
    }, this.updateInterval);
    
    console.log(`✅ Keuangan Progress Updater initialized for ${this.keuanganIds.length} item(s)`);
  }

  /**
   * Update progress untuk semua keuangan
   */
  async updateAllProgress() {
    for (const keuanganId of this.keuanganIds) {
      await this.updateProgress(keuanganId);
    }
  }

  /**
   * Update progress untuk satu keuangan_id
   */
  async updateProgress(keuanganId) {
    try {
      const response = await fetch(`/api/get_keuangan_progress.php?keuangan_id=${keuanganId}`);
      const result = await response.json();
      
      if (result.success) {
        this.renderProgress(keuanganId, result.data);
      } else {
        console.error(`❌ Failed to update progress for keuangan_id ${keuanganId}:`, result.error);
      }
    } catch (error) {
      console.error(`❌ Error updating progress for keuangan_id ${keuanganId}:`, error);
    }
  }

  /**
   * Render/update UI dengan data progress terbaru
   */
  renderProgress(keuanganId, data) {
    const containerId = `keuangan-${keuanganId}`;
    
    // Update Total Terbayar
    const terbayarEl = document.querySelector(`#${containerId} .total-terbayar`);
    if (terbayarEl) {
      terbayarEl.textContent = this.formatRupiah(data.total_bayar);
    }
    
    const bulanTerbayarEl = document.querySelector(`#${containerId} .bulan-terbayar`);
    if (bulanTerbayarEl) {
      bulanTerbayarEl.textContent = `${data.stats.pembayaran_valid} dari 12 bulan`;
    }
    
    // Update Tunggakan
    const tunggakanEl = document.querySelector(`#${containerId} .sisa-tunggakan`);
    if (tunggakanEl) {
      tunggakanEl.textContent = this.formatRupiah(data.sisa_tunggakan);
    }
    
    const bulanTersisaEl = document.querySelector(`#${containerId} .bulan-tersisa`);
    if (bulanTersisaEl) {
      const bulanTersisa = 12 - data.stats.pembayaran_valid;
      bulanTersisaEl.textContent = `${bulanTersisa} bulan tersisa`;
    }
    
    // Update Progress
    const progressValueEl = document.querySelector(`#${containerId} .progress-value`);
    if (progressValueEl) {
      progressValueEl.textContent = `${data.progress.toFixed(1)}%`;
    }
    
    const progressBarEl = document.querySelector(`#${containerId} .progress-bar`);
    if (progressBarEl) {
      progressBarEl.style.width = `${data.progress}%`;
      
      // Update class berdasarkan progress
      progressBarEl.className = 'progress-bar';
      if (data.progress >= 100) {
        progressBarEl.classList.add('bg-success');
      } else if (data.progress >= 50) {
        progressBarEl.classList.add('bg-warning');
      } else {
        progressBarEl.classList.add('bg-danger');
      }
    }
    
    // Update Status Footer
    const statusBadgeEl = document.querySelector(`#${containerId} .status-badge`);
    if (statusBadgeEl) {
      if (data.is_lunas) {
        statusBadgeEl.className = 'badge bg-success status-badge';
        statusBadgeEl.innerHTML = '<i class="bi bi-check-circle me-1"></i> LUNAS';
      } else {
        statusBadgeEl.className = 'badge bg-warning text-dark status-badge';
        statusBadgeEl.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> BELUM LUNAS';
      }
    }
    
    // Update total terbayar di footer
    const footerTerbayarEl = document.querySelector(`#${containerId} .footer-terbayar`);
    if (footerTerbayarEl) {
      footerTerbayarEl.textContent = this.formatRupiah(data.total_bayar);
    }
    
    // Update sisa di footer
    const footerSisaEl = document.querySelector(`#${containerId} .footer-sisa`);
    if (footerSisaEl) {
      if (data.sisa_tunggakan > 0) {
        footerSisaEl.parentElement.style.display = '';
        footerSisaEl.textContent = this.formatRupiah(data.sisa_tunggakan);
      } else {
        footerSisaEl.parentElement.style.display = 'none';
      }
    }
    
    console.log(`✅ Progress updated for keuangan_id ${keuanganId}: ${data.progress.toFixed(1)}%`);
  }

  /**
   * Format number ke format Rupiah
   */
  formatRupiah(number) {
    return new Intl.NumberFormat('id-ID').format(number);
  }

  /**
   * Stop auto-update
   */
  stop() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
      console.log('⏸️ Keuangan Progress Updater stopped');
    }
  }

  /**
   * Force update sekarang (manual trigger)
   */
  forceUpdate() {
    console.log('🔄 Force updating progress...');
    this.updateAllProgress();
  }
}

// Global instance
window.keuanganProgressUpdater = new KeuanganProgressUpdater();
