document.addEventListener('DOMContentLoaded', () => {
    const routeForm = document.getElementById('routeForm');
    const block2 = document.getElementById('block2');
    const block3 = document.getElementById('block3');
    
    // Установка минимальной даты (10 дней от текущей)
    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 10);
    document.getElementById('departure_date').min = minDate.toISOString().split('T')[0];

    routeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            start_point: document.getElementById('start_point').value,
            end_point: document.getElementById('end_point').value,
            departure_date: document.getElementById('departure_date').value,
            departure_time: document.getElementById('departure_time').value
        };

        try {
            const response = await fetch('http://mysite.loc/api/routes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (result.status === false) {
                alert(result.error || 'Ошибка при поиске маршрутов');
                return;
            }

            displayRoutes(result.data);
            block2.style.display = 'block';
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Ошибка при поиске маршрутов');
        }
    });

    function displayRoutes(routes) {
        const routesList = document.getElementById('routesList');
        routesList.innerHTML = '';

        routes.forEach((route, index) => {
            const isFastest = index === 0;
            const routeElement = document.createElement('div');
            routeElement.className = `route-item ${isFastest ? 'fastest' : ''}`;
            
            routeElement.innerHTML = `
                <h3>Маршрут ${route.id} 
                    ${isFastest ? '<span class="fastest-label">Самый быстрый</span>' : ''}
                </h3>
                <p>Отправление: ${route.departure_time}</p>
                <p>Время в пути: ${route.total_time} минут</p>
                <p>Стоимость: ${route.price} руб.</p>
            `;

            routeElement.addEventListener('click', () => selectRoute(route));
            routesList.appendChild(routeElement);
        });
    }

    function selectRoute(route) {
        block3.style.display = 'block';
        displayStops(route.stops);
    }

    function displayStops(stops) {
        const stopsList = document.getElementById('stopsList');
        stopsList.innerHTML = '';

        stops.forEach(stop => {
            const stopElement = document.createElement('div');
            stopElement.className = 'stop-item';
            stopElement.innerHTML = `
                <label>
                    <input type="checkbox" name="stop" value="${stop.id}">
                    ${stop.name}
                </label>
                <select class="stop-duration" style="display: none;">
                    <option value="0">Без остановки</option>
                    <option value="2">2 часа</option>
                    <option value="3">3 часа</option>
                    <option value="4">4 часа</option>
                    <option value="5">5 часов</option>
                    <option value="6">6 часов</option>
                </select>
            `;

            const checkbox = stopElement.querySelector('input[type="checkbox"]');
            const select = stopElement.querySelector('select');

            checkbox.addEventListener('change', () => {
                select.style.display = checkbox.checked ? 'block' : 'none';
                calculateArrivalTime();
            });

            select.addEventListener('change', calculateArrivalTime);
            stopsList.appendChild(stopElement);
        });
    }

    function calculateArrivalTime() {
        // Здесь добавьте расчет времени прибытия
        // и проверку времени прибытия (00:00 - 06:00)
    }

    document.getElementById('submitOrder').addEventListener('click', async () => {
        // Здесь добавьте создание заказа
    });
}); 