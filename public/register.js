document.getElementById('registrationForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('http://mysite.loc/api/users', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status) {
            alert('Регистрация успешна!');
            window.location.href = '/public/login.html';
        } else {
            alert(result.error || 'Ошибка при регистрации');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при регистрации');
    }
}); 