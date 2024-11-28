document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('http://mysite.loc/api/users/login', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            alert('Вход выполнен успешно!');
            // Сохраняем данные пользователя в localStorage
            localStorage.setItem('userInn', formData.get('inn'));
            window.location.href = '/profile.html'; // Редирект в личный кабинет
        } else {
            alert(result.error || 'Неверный ИНН или пароль');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при входе');
    }
}); 