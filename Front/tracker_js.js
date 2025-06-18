async function fetchTelemetry() {
  try {
    const response = await fetch('data_dummy.php');
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    const data = await response.json();

    const rpm = data.rpm || 0;
    const speed = data.speed_kmh || 0;
    const turbo = data.turbo || 0;

    document.getElementById('rpm_val').textContent = rpm;
    document.getElementById('speed_val').textContent = speed + ' km/u';
    document.getElementById('turbo_val').textContent = turbo.toFixed(1);

    document.getElementById('gear_val').textContent = data.gear ?? '-';
    document.getElementById('throttle_val').textContent = Math.round((data.throttle || 0) * 100) + '%';
    document.getElementById('brake_val').textContent = Math.round((data.brake || 0) * 100) + '%';

    document.getElementById('lapTime').textContent = (data.lap_time_ms ?? 0) + ' ms';
    document.getElementById('lastLap').textContent = (data.last_lap_ms ?? 0) + ' ms';
    document.getElementById('bestLap').textContent = (data.best_lap_ms ?? 0) + ' ms';
    document.getElementById('lapCount').textContent = data.lap_count ?? 0;
    document.getElementById('trackName').textContent = data.track_name ?? '-';
    document.getElementById('datetime').textContent = data.datetime ?? '-';

    updateGauge('rpm', rpm, 15000);
    updateGauge('speed', speed, 400);
    updateGauge('turbo', turbo, 2);
    updateGauge('gear', data.gear ? Number(data.gear) : 0, 7);
    updateGauge('throttle', data.throttle || 0, 1);
    updateGauge('brake', data.brake || 0, 1);
  } catch (err) {
    console.error('Error fetching telemetry:', err);
  }
}

function updateGauge(id, value, max) {
  const circle = document.getElementById(id);
  if (!circle) return;

  const radius = 15.9155;
  const circumference = 2 * Math.PI * radius;
  const percent = Math.min(value / max, 1);
  const offset = circumference * (1 - percent);

  circle.style.strokeDasharray = circumference;
  circle.style.strokeDashoffset = offset;

  // Dynamische kleur op basis van percentage
  let color;
  if (percent < 0.5) {
    // Groen naar oranje
    const red = Math.floor(510 * percent);     // tot max 255 bij 50%
    const green = 255;
    color = `rgb(${red},${green},0)`;
  } else {
    // Oranje naar rood
    const red = 255;
    const green = Math.floor(510 * (1 - percent)); // tot min 0 bij 100%
    color = `rgb(${red},${green},0)`;
  }

  circle.style.stroke = color;
}

setInterval(fetchTelemetry, 2500);
fetchTelemetry();
