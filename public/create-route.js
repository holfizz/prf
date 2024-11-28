document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('createRouteForm');
    const addStopBtn = document.getElementById('addStop');
    const stopsList = document.getElementById('stopslist');

    // Установка минимальной даты
    const today = new Date();
    document.getElementById('departure_date').min = today.toISOString().split('T')[0];

    addStopBtn.addEventListener('click', () => {
        const stopDiv = document.createElement('div');
        stopDiv.className = 'form-group';
        stopDiv.innerHTML = `
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <input type="text" placeholder="Название города" required>
                <input type="text" placeholder="Координаты" required>
                <button type="button" class="remove-stop">Удалить</button>
            </div>
        `;

        stopDiv.querySelector('.remove-stop').addEventListener('click', () => {
            stopDiv.remove();
        });

        stopsList.appendChild(stopDiv);
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const stops = [];
        stopsList.querySelectorAll('.form-group').forEach(stop => {
            const [cityInput, coordsInput] = stop.querySelectorAll('input');
            if (cityInput.value && coordsInput.value) {
                stops.push({
                    name: cityInput.value,
                    coordinates: coordsInput.value
                });
            }
        });

        const routeData = {
            start_point: document.getElementById('start_point').value,
            end_point: document.getElementById('end_point').value,
            departure_date: document.getElementById('departure_date').value,
            departure_time: document.getElementById('departure_time').value,
            total_time: parseInt(document.getElementById('total_time').value),
            price: parseFloat(document.getElementById('price').value),
            stops: stops
        };

        try {
            console.log('Отправляемые данные:', routeData); // Отладка

            const response = await fetch('http://mysite.loc/api/routes/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(routeData)
            });

            const result = await response.json();
            console.log('Полученный ответ:', result); // Отладка

            if (result.status) {
                alert(`Маршрут успешно создан!\nID маршрута: ${result.data.id}\nМаршрут: ${result.data.start_point} → ${result.data.end_point}`);
                form.reset();
                stopsList.innerHTML = '';
            } else {
                console.error('Ошибка:', result);
                alert(`Ошибка при создании маршрута:\n${result.error}\nMySQL Error: ${result.mysql_error}`);
            }
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при создании маршрута');
        }
    });
}); 