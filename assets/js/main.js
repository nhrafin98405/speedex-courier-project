/**
 * SpeedEx Courier Service - Main JavaScript
 * Handles theme switching, animations, charts, and interactions
 */

// ============================================
// THEME MANAGEMENT
// ============================================
const ThemeManager = {
  init() {
    const saved = localStorage.getItem('speedex-theme') || 'dark';
    this.setTheme(saved);
    document.querySelectorAll('.theme-toggle, .theme-switcher').forEach(el => {
      el.addEventListener('click', () => this.toggle());
    });
  },

  setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('speedex-theme', theme);
    document.querySelectorAll('.theme-label').forEach(el => {
      el.textContent = theme === 'dark' ? 'Dark' : 'Light';
    });
  },

  toggle() {
    const current = document.documentElement.getAttribute('data-theme');
    this.setTheme(current === 'dark' ? 'light' : 'dark');
  }
};

// ============================================
// ANIMATED COUNTERS
// ============================================
const CounterAnimation = {
  animate(element) {
    const target = parseInt(element.getAttribute('data-count'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const update = () => {
      current += step;
      if (current < target) {
        element.textContent = Math.floor(current).toLocaleString();
        requestAnimationFrame(update);
      } else {
        element.textContent = target.toLocaleString();
      }
    };
    update();
  },

  init() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
          entry.target.classList.add('counted');
          this.animate(entry.target);
        }
      });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(el => observer.observe(el));
  }
};

// ============================================
// SCROLL REVEAL
// ============================================
const ScrollReveal = {
  init() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  }
};

// ============================================
// FAQ ACCORDION
// ============================================
const FAQAccordion = {
  init() {
    document.querySelectorAll('.faq-question').forEach(q => {
      q.addEventListener('click', () => {
        const item = q.closest('.faq-item');
        const isActive = item.classList.contains('active');

        // Close all
        document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));

        // Open clicked if it wasn't active
        if (!isActive) item.classList.add('active');
      });
    });
  }
};

// ============================================
// SIDEBAR
// ============================================
const Sidebar = {
  init() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggle && sidebar) {
      toggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        if (overlay) overlay.classList.toggle('show');
      });
    }

    if (overlay) {
      overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
      });
    }
  }
};

// ============================================
// FORM VALIDATION
// ============================================
const FormValidator = {
  init() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
      form.addEventListener('submit', (e) => {
        if (!this.validate(form)) {
          e.preventDefault();
        }
      });
    });
  },

  validate(form) {
    let isValid = true;
    form.querySelectorAll('[required]').forEach(field => {
      this.clearError(field);
      if (!field.value.trim()) {
        this.showError(field, 'This field is required');
        isValid = false;
      } else if (field.type === 'email' && !this.isEmail(field.value)) {
        this.showError(field, 'Please enter a valid email');
        isValid = false;
      }
    });
    return isValid;
  },

  showError(field, message) {
    field.classList.add('is-invalid');
    const error = document.createElement('div');
    error.className = 'invalid-feedback';
    error.style.cssText = 'color: #ef4444; font-size: 0.75rem; margin-top: 4px;';
    error.textContent = message;
    field.parentNode.appendChild(error);
  },

  clearError(field) {
    field.classList.remove('is-invalid');
    const error = field.parentNode.querySelector('.invalid-feedback');
    if (error) error.remove();
  },

  isEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
};

// ============================================
// TOAST NOTIFICATIONS
// ============================================
const Toast = {
  container: null,

  init() {
    this.container = document.createElement('div');
    this.container.className = 'toast-container';
    document.body.appendChild(this.container);
  },

  show(message, type = 'success', duration = 4000) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle' };
    const colors = { success: '#22c55e', error: '#ef4444', warning: '#eab308' };

    toast.innerHTML = `
      <i class="fas ${icons[type]}" style="color: ${colors[type]}; font-size: 1.25rem;"></i>
      <div>
        <div style="font-weight: 600; font-size: 0.875rem;">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
        <div style="font-size: 0.8rem; color: var(--text-secondary);">${message}</div>
      </div>
      <button onclick="this.closest('.toast').remove()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;margin-left:auto;">
        <i class="fas fa-times"></i>
      </button>
    `;

    this.container.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'slide-in 0.3s ease reverse';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  }
};

// ============================================
// PARCEL PRICE CALCULATOR
// ============================================
const PriceCalculator = {
  rates: {
    standard: { base: 60, perKg: 15 },
    express: { base: 100, perKg: 25 },
    same_day: { base: 200, perKg: 40 }
  },

  calculate(weight, deliveryType) {
    const rate = this.rates[deliveryType] || this.rates.standard;
    return rate.base + (weight * rate.perKg);
  },

  init() {
    const weightInput = document.getElementById('parcelWeight');
    const typeSelect = document.getElementById('deliveryType');
    const priceDisplay = document.getElementById('deliveryCharge');

    if (weightInput && typeSelect && priceDisplay) {
      const update = () => {
        const weight = parseFloat(weightInput.value) || 0;
        const type = typeSelect.value || 'standard';
        const price = this.calculate(weight, type);
        priceDisplay.textContent = '৳ ' + price.toFixed(2);
      };

      weightInput.addEventListener('input', update);
      typeSelect.addEventListener('change', update);
    }
  }
};

// ============================================
// PASSWORD TOGGLE
// ============================================
const PasswordToggle = {
  init() {
    document.querySelectorAll('.toggle-password').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = btn.closest('.input-group').querySelector('input');
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });
    });
  }
};

// ============================================
// SEARCH FUNCTIONALITY
// ============================================
const SearchHandler = {
  init() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    let timeout;
    searchInput.addEventListener('input', (e) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        const query = e.target.value.trim();
        if (query.length > 2) {
          this.search(query);
        }
      }, 300);
    });
  },

  search(query) {
    console.log('Searching:', query);
    // Implement AJAX search
  }
};

// ============================================
// SMOOTH SCROLLING
// ============================================
const SmoothScroll = {
  init() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  }
};

// ============================================
// PARCEL TRACKING
// ============================================
const ParcelTracker = {
  init() {
    const form = document.getElementById('trackingForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const trackingId = document.getElementById('trackingInput').value.trim();
      if (trackingId) {
        this.track(trackingId);
      }
    });
  },

  async track(trackingId) {
    const resultsDiv = document.getElementById('trackingResults');
    if (!resultsDiv) return;

    // Show loading
    resultsDiv.innerHTML = `
      <div class="text-center py-5">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-3" style="color: var(--text-secondary);">Tracking parcel...</p>
      </div>
    `;
    resultsDiv.style.display = 'block';

    try {
      const response = await fetch(`api/track.php?id=${encodeURIComponent(trackingId)}`);
      const data = await response.json();

      if (data.success) {
        this.displayResults(data.parcel, data.tracking);
      } else {
        resultsDiv.innerHTML = `
          <div class="text-center py-5">
            <i class="fas fa-box-open" style="font-size: 3rem; color: var(--text-muted);"></i>
            <p class="mt-3" style="color: var(--text-secondary);">No parcel found with this tracking ID</p>
          </div>
        `;
      }
    } catch (err) {
      resultsDiv.innerHTML = `
        <div class="text-center py-5">
          <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #ef4444;"></i>
          <p class="mt-3" style="color: var(--text-secondary);">Error tracking parcel. Please try again.</p>
        </div>
      `;
    }
  },

  displayResults(parcel, tracking) {
    const resultsDiv = document.getElementById('trackingResults');
    const statuses = ['pending', 'picked_up', 'in_transit', 'at_hub', 'out_for_delivery', 'delivered'];
    const statusLabels = {
      pending: 'Pending', picked_up: 'Picked Up', in_transit: 'In Transit',
      at_hub: 'At Hub', out_for_delivery: 'Out for Delivery', delivered: 'Delivered'
    };
    const statusIcons = {
      pending: 'fa-clock', picked_up: 'fa-hand-holding-box', in_transit: 'fa-truck',
      at_hub: 'fa-warehouse', out_for_delivery: 'fa-motorcycle', delivered: 'fa-check-circle'
    };

    const currentIndex = statuses.indexOf(parcel.status);

    let html = `
      <div class="glass-card mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
          <div>
            <h5 class="mb-1">Tracking ID: <span class="text-primary-green">${parcel.tracking_id}</span></h5>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">
              ${parcel.sender_name} → ${parcel.receiver_name}
            </p>
          </div>
          <span class="badge-status badge-${parcel.status.replace('_', '-')}">${statusLabels[parcel.status]}</span>
        </div>
        <div class="tracking-timeline">
    `;

    statuses.forEach((status, i) => {
      const isCompleted = i < currentIndex;
      const isActive = i === currentIndex;
      const cls = isCompleted ? 'completed' : isActive ? 'active' : '';

      html += `
        <div class="tracking-step ${cls}">
          <div class="tracking-dot">
            <i class="fas ${statusIcons[status] || 'fa-circle'}"></i>
          </div>
          <div>
            <h6 class="mb-1" style="font-size: 0.9rem;">${statusLabels[status]}</h6>
            <p style="color: var(--text-muted); font-size: 0.8rem;">
              ${isCompleted || isActive ? 'Completed' : 'Pending'}
            </p>
          </div>
        </div>
      `;
    });

    html += '</div></div>';
    resultsDiv.innerHTML = html;
  }
};

// ============================================
// CHART INITIALIZATION
// ============================================
const DashboardCharts = {
  colors: {
    green: '#22c55e',
    blue: '#3b82f6',
    yellow: '#eab308',
    red: '#ef4444',
    purple: '#a855f7',
    greenAlpha: 'rgba(34, 197, 94, 0.2)',
    blueAlpha: 'rgba(59, 130, 246, 0.2)'
  },

  initParcelOverview(canvasId) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Delivered', 'In Transit', 'Pending'],
        datasets: [{
          data: [892, 320, 36],
          backgroundColor: [this.colors.green, this.colors.blue, this.colors.yellow],
          borderWidth: 0,
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#94a3b8',
              padding: 16,
              usePointStyle: true,
              font: { family: 'Poppins', size: 12 }
            }
          }
        }
      }
    });
  },

  initDeliveryPerformance(canvasId) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Deliveries',
          data: [120, 190, 300, 500, 420, 380, 520, 610, 580, 740, 850, 920],
          borderColor: this.colors.green,
          backgroundColor: this.colors.greenAlpha,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointBackgroundColor: this.colors.green,
          pointBorderWidth: 2,
          pointBorderColor: '#071018'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            grid: { color: 'rgba(255,255,255,0.05)' },
            ticks: { color: '#64748b', font: { family: 'Poppins', size: 11 } }
          },
          y: {
            grid: { color: 'rgba(255,255,255,0.05)' },
            ticks: { color: '#64748b', font: { family: 'Poppins', size: 11 } }
          }
        },
        plugins: {
          legend: {
            labels: { color: '#94a3b8', font: { family: 'Poppins' } }
          }
        }
      }
    });
  },

  initTopRoutes(canvasId) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Dhaka → Mymensingh', 'Dhaka → Sylhet', 'Chittagong → Dhaka', 'Dhaka → Khulna', 'Rajshahi → Dhaka'],
        datasets: [{
          label: 'Parcels',
          data: [220, 210, 150, 120, 80],
          backgroundColor: [this.colors.green, this.colors.blue, this.colors.purple, this.colors.yellow, this.colors.red],
          borderRadius: 8,
          borderSkipped: false,
          barThickness: 24
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: {
          x: {
            grid: { color: 'rgba(255,255,255,0.05)' },
            ticks: { color: '#64748b', font: { family: 'Poppins', size: 11 } }
          },
          y: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { family: 'Poppins', size: 11 } }
          }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  },

  initHubOverview(canvasId) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Delivered', 'In Transit', 'Pending'],
        datasets: [{
          data: [980, 75, 45],
          backgroundColor: [this.colors.green, this.colors.blue, this.colors.yellow],
          borderWidth: 0,
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#94a3b8',
              padding: 16,
              usePointStyle: true,
              font: { family: 'Poppins', size: 12 }
            }
          }
        }
      }
    });
  },

  initMonthlyRevenue(canvasId) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue (৳)',
          data: [45000, 62000, 78000, 95000, 88000, 120000],
          backgroundColor: this.colors.greenAlpha,
          borderColor: this.colors.green,
          borderWidth: 2,
          borderRadius: 8,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            grid: { color: 'rgba(255,255,255,0.05)' },
            ticks: { color: '#64748b', font: { family: 'Poppins', size: 11 } }
          },
          y: {
            grid: { color: 'rgba(255,255,255,0.05)' },
            ticks: { color: '#64748b', font: { family: 'Poppins', size: 11 }, callback: v => '৳' + (v/1000) + 'k' }
          }
        },
        plugins: {
          legend: { labels: { color: '#94a3b8', font: { family: 'Poppins' } } }
        }
      }
    });
  }
};

// ============================================
// INITIALIZE EVERYTHING
// ============================================
document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
  CounterAnimation.init();
  ScrollReveal.init();
  FAQAccordion.init();
  Sidebar.init();
  FormValidator.init();
  Toast.init();
  PriceCalculator.init();
  PasswordToggle.init();
  SearchHandler.init();
  SmoothScroll.init();
  ParcelTracker.init();

  // Init charts if canvas elements exist
  DashboardCharts.initParcelOverview('parcelOverviewChart');
  DashboardCharts.initDeliveryPerformance('deliveryChart');
  DashboardCharts.initTopRoutes('topRoutesChart');
  DashboardCharts.initHubOverview('hubOverviewChart');
  DashboardCharts.initMonthlyRevenue('revenueChart');
});
