document.querySelectorAll('.section h1').forEach(h1 => {
    h1.addEventListener('mouseover', function() {
        const content = this.nextElementSibling.innerHTML;
        const mainContent = document.querySelector('.main-content');
        mainContent.innerHTML = `<div class="inner-content">${content}</div>`;
        mainContent.style.opacity = 1; // Hiển thị nội dung khi hover
    });
    
    h1.addEventListener('mouseleave', function() {
        document.querySelector('.main-content').style.opacity = 0; // Ẩn nội dung khi không hover
    });
});
