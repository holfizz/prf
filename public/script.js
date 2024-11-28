const getPost = () => {
    let posts = []
    fetch('https://jsonplaceholder.typicode.com/posts')
        .then(res => res.json())
        .then(data => {
            posts = data
            renderPosts(posts)
        })
}

const renderPosts = (posts) => {
    posts.forEach((item) => {
        document.querySelector('.cards').innerHTML += `
        <div class="card">
            <h1>${item.title}</h1>
            <p>${item.body}</p>
        </div>
        `
    })
}

getPost()