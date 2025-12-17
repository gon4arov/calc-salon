;(function() {
  var cfg = null;
  var devicesById = {};
  var state = {
    workingDays: 24,
    rent: 0,
    utilities: 0,
    devices: []
  };

  var els = {};

  function qs(id) { return document.getElementById(id); }

  function init() {
    if (!window.MLSalonCalcConfig) {
      setTimeout(init, 50);
      return;
    }
    cfg = window.MLSalonCalcConfig;
    devicesById = {};
    (cfg.devices || []).forEach(function(d) {
      // cost_raw приоритетен как число, cost — форматированная строка
      devicesById[d.id] = {
        id: d.id,
        name: d.name,
        costRaw: typeof d.cost_raw !== 'undefined' ? d.cost_raw : (parseFloat(d.cost) || 0),
        costLabel: d.cost || ''
      };
    });
    state.workingDays = cfg.defaults && cfg.defaults.working_days ? cfg.defaults.working_days : 24;
    state.rent = cfg.defaults && cfg.defaults.rent ? cfg.defaults.rent : 0;
    state.utilities = cfg.defaults && cfg.defaults.utilities ? cfg.defaults.utilities : 0;
    els.presets = qs('ml-salon-presets');
    els.procedures = qs('ml-salon-procedures');
    els.suggestions = qs('ml-salon-suggestions');
    els.deviceSelect = qs('ml-salon-device-select');
    els.deviceRows = qs('ml-salon-device-rows');
    els.workDays = qs('ml-salon-working-days');
    els.rent = qs('ml-salon-rent');
    els.utilities = qs('ml-salon-utilities');
    els.totals = document.querySelector('#ml-salon-totals');
    els.email = qs('ml-salon-email');
    els.emailStatus = qs('ml-salon-email-status');

    renderPresets();
    renderProcedures();
    renderDeviceSelect();
    attachInputs();

    // Стартовый пресет
    if (cfg.presets && cfg.presets[0]) {
      applyPreset(cfg.presets[0].id);
    } else {
      renderDevices();
      recalc();
    }

    qs('ml-salon-add-device').addEventListener('click', function() {
      var id = els.deviceSelect.value;
      addDevice(id);
    });

    qs('ml-salon-send').addEventListener('click', sendEmail);
  }

  function renderPresets() {
    if (!els.presets) return;
    els.presets.innerHTML = '';
    (cfg.presets || []).forEach(function(preset) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ml-salon__preset';
      btn.textContent = preset.name;
      btn.addEventListener('click', function() {
        applyPreset(preset.id);
      });
      els.presets.appendChild(btn);
    });
  }

  function renderProcedures() {
    if (!els.procedures) return;
    els.procedures.innerHTML = '';
    (cfg.procedures || []).forEach(function(proc) {
      var label = document.createElement('label');
      label.className = 'ml-salon__procedure';
      var input = document.createElement('input');
      input.type = 'checkbox';
      input.value = proc.id;
      input.addEventListener('change', updateSuggestions);
      label.appendChild(input);
      label.appendChild(document.createTextNode(proc.name));
      els.procedures.appendChild(label);
    });
  }

  function renderDeviceSelect() {
    els.deviceSelect.innerHTML = '';
    (cfg.devices || []).forEach(function(d) {
      var opt = document.createElement('option');
      opt.value = d.id;
      opt.textContent = d.name;
      els.deviceSelect.appendChild(opt);
    });
  }

  function applyPreset(id) {
    var preset = (cfg.presets || []).find(function(p) { return p.id === id; });
    if (!preset) return;
    state.workingDays = preset.working_days || state.workingDays;
    state.rent = preset.rent || state.rent;
    state.utilities = preset.utilities || state.utilities;
    state.devices = [];
    (preset.devices || []).forEach(function(devId) {
      addDevice(devId, true);
    });
    els.workDays.value = state.workingDays;
    els.rent.value = state.rent;
    els.utilities.value = state.utilities;
    renderDevices();
    recalc();
  }

  function updateSuggestions() {
    var checked = Array.prototype.slice.call(els.procedures.querySelectorAll('input:checked')).map(function(i) { return i.value; });
    els.suggestions.innerHTML = '';
    if (!checked.length) return;
    var suggested = (cfg.devices || []).filter(function(d) {
      return (d.tags || []).some(function(tag) { return checked.indexOf(tag) !== -1; });
    });
    suggested.forEach(function(dev) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ml-salon__chip';
      btn.textContent = '+ ' + dev.name;
      btn.addEventListener('click', function() {
        addDevice(dev.id);
      });
      els.suggestions.appendChild(btn);
    });
  }

  function addDevice(id, skipRender) {
    if (!devicesById[id]) return;
    var exists = state.devices.some(function(d) { return d.id === id; });
    if (exists) return;
    var dev = devicesById[id];
    state.devices.push({
      id: id,
      clients: dev.clients || 0,
      price: dev.price || 0
    });
    if (!skipRender) {
      renderDevices();
      syncProceduresFromDevices();
      updateSuggestions();
      recalc();
    }
  }

  function removeDevice(id) {
    state.devices = state.devices.filter(function(d) { return d.id !== id; });
    renderDevices();
    syncProceduresFromDevices();
    updateSuggestions();
    recalc();
  }

  function renderDevices() {
    els.deviceRows.innerHTML = '';
    state.devices.forEach(function(d) {
      var meta = devicesById[d.id];
      var tr = document.createElement('tr');
      tr.innerHTML = [
        '<td>' + (meta ? meta.name : d.id) + '</td>',
        '<td><input type="number" class="ml-salon__num" data-field="clients" data-id="' + d.id + '" min="0" step="0.5" value="' + d.clients + '"></td>',
        '<td><input type="number" class="ml-salon__num" data-field="price" data-id="' + d.id + '" min="0" step="100" value="' + d.price + '"></td>',
        '<td>' + (meta ? (meta.costLabel || formatMoney(meta.costRaw)) : '—') + '</td>',
        '<td data-field="revenue" data-id="' + d.id + '">—</td>',
        '<td><button type="button" class="ml-salon__link" data-remove="' + d.id + '">' + cfg.lang.remove + '</button></td>'
      ].join('');
      els.deviceRows.appendChild(tr);
    });

    els.deviceRows.querySelectorAll('.ml-salon__num').forEach(function(input) {
      input.addEventListener('input', onDeviceInput);
    });
    els.deviceRows.querySelectorAll('[data-remove]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        removeDevice(btn.getAttribute('data-remove'));
      });
    });

    syncProceduresFromDevices();
    updateSuggestions();
  }

  function onDeviceInput(e) {
    var id = e.target.getAttribute('data-id');
    var field = e.target.getAttribute('data-field');
    var val = parseFloat(e.target.value);
    if (isNaN(val) || val < 0) val = 0;
    state.devices = state.devices.map(function(d) {
      if (d.id === id) {
        d[field] = val;
      }
      return d;
    });
    recalc();
  }

  function attachInputs() {
    els.workDays.addEventListener('input', function() {
      var v = parseInt(els.workDays.value, 10);
      if (isNaN(v) || v < 1) v = 1;
      state.workingDays = v;
      recalc();
    });
    els.rent.addEventListener('input', function() {
      var v = parseFloat(els.rent.value) || 0;
      state.rent = v;
      recalc();
    });
    els.utilities.addEventListener('input', function() {
      var v = parseFloat(els.utilities.value) || 0;
      state.utilities = v;
      recalc();
    });
  }

  function recalc() {
    var totalCapex = 0;
    var totalRevenue = 0;
    state.devices.forEach(function(d) {
      var meta = devicesById[d.id];
      if (!meta) return;
      totalCapex += meta.costRaw || 0;
      var revenue = (d.clients || 0) * (d.price || 0) * (state.workingDays || 0);
      totalRevenue += revenue;
      var cell = els.deviceRows.querySelector('[data-field="revenue"][data-id="' + d.id + '"]');
      if (cell) cell.textContent = formatMoney(revenue);
    });

    var expenses = (state.rent || 0) + (state.utilities || 0);
    var profit = totalRevenue - expenses;
    var payback = profit > 0 ? (totalCapex / profit) : null;

    updateTotalField('capex', formatMoney(totalCapex));
    updateTotalField('revenue', formatMoney(totalRevenue));
    updateTotalField('profit', formatMoney(profit));
    updateTotalField('payback', payback ? payback.toFixed(1) + ' ' + cfg.lang.months : '—');
  }

  function updateTotalField(field, value) {
    var el = els.totals.querySelector('[data-field="' + field + '"]');
    if (el) el.textContent = value;
  }

  function formatMoney(value) {
    var v = parseFloat(value) || 0;
    return v.toLocaleString('ru-RU') + ' ' + cfg.lang.currency;
  }

  function buildPayload() {
    var lines = [];
    lines.push('Рабочих дней: ' + state.workingDays);
    lines.push('Аренда: ' + state.rent);
    lines.push('Коммунальные: ' + state.utilities);
    lines.push('Аппараты:');
    state.devices.forEach(function(d) {
      var meta = devicesById[d.id];
      lines.push('- ' + (meta ? meta.name : d.id) + ': ' + d.clients + ' кл./день, ' + d.price + ' цена, капекс ' + (meta ? meta.cost : '—'));
    });
    return lines.join('\n');
  }

  function sendEmail() {
    if (!cfg.action_send) return;
    var email = (els.email.value || '').trim();
    if (!email) {
      showEmailStatus(cfg.lang.email_required, true);
      return;
    }
    var payload = buildPayload();
    showEmailStatus('', false);
    $.ajax({
      url: cfg.action_send,
      type: 'post',
      dataType: 'json',
      data: { email: email, payload: payload },
      success: function(res) {
        if (res && res.success) {
          showEmailStatus(cfg.lang.email_success, false);
        } else {
          showEmailStatus(cfg.lang.email_error, true);
        }
      },
      error: function() {
        showEmailStatus(cfg.lang.email_error, true);
      }
    });
  }

  function showEmailStatus(msg, isError) {
    if (!els.emailStatus) return;
    els.emailStatus.textContent = msg;
    els.emailStatus.className = 'ml-salon__email-status ' + (isError ? 'is-error' : 'is-ok');
  }

  function syncProceduresFromDevices() {
    var tags = new Set();
    state.devices.forEach(function(d) {
      var meta = devicesById[d.id];
      if (meta && Array.isArray(meta.tags)) {
        meta.tags.forEach(function(t) { tags.add(t); });
      }
    });
    if (!els.procedures) return;

    // Сброс класса is-covered
    els.procedures.querySelectorAll('.ml-salon__procedure').forEach(function(lbl) {
      lbl.classList.remove('is-covered');
    });

    els.procedures.querySelectorAll('input[type="checkbox"]').forEach(function(input) {
      if (tags.has(input.value)) {
        input.checked = true;
        if (input.parentElement && input.parentElement.classList.contains('ml-salon__procedure')) {
          input.parentElement.classList.add('is-covered');
        }
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
