function updateGauge(id, value, maxValue, unit) {
    const gauge = document.getElementById(id);
    const circle = gauge.querySelector('.circle');
    const text = gauge.querySelector('.percentage');

    // Bereken percentage
    const percent = Math.min(100, Math.max(0, (value / maxValue) * 100));
    circle.setAttribute('stroke-dasharray', `${percent}, 100`);

    // Tekst bijwerken
    text.textContent = `${Math.round(value)} ${unit}`;

    // Kleur bepalen op basis van percentage
    let color;
    if (percent < 40) {
        color = 'limegreen';
    } else if (percent < 75) {
        color = 'orange';
    } else {
        color = 'red';
    }
    circle.setAttribute('stroke', color);
}

// Voorbeeldwaarden (kun je ook vervangen met live data of sliders)
let speed = 0;
let fuel = 100;
let rpm = 0;

function simulate() {
    // Dummy simulatie met willekeurige fluctuatie
    speed = Math.random() * 200;
    fuel = Math.max(0, fuel - Math.random() * 2);
    rpm = Math.random() * 7000;

    updateGauge('speedGauge', speed, 200, 'km/u');
    updateGauge('fuelGauge', fuel, 100, '%');
    updateGauge('rpmGauge', rpm, 7000, 'RPM');
}

setInterval(simulate, 1000); // Elke seconde updaten
