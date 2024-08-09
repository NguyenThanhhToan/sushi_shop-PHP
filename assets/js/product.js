document.addEventListener("DOMContentLoaded", function() {
    const addToCartButtons = document.querySelectorAll(".add-to-cart");

    addToCartButtons.forEach(function(button) {
        button.addEventListener("click", function() {
            const productId = this.getAttribute("data-product-id");
            const quantityInput = document.querySelector(`#quantity-input-${productId}`);
            const quantity = quantityInput ? quantityInput.value : 1;

            fetch("../pages/add_to_cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "product_id=" + encodeURIComponent(productId) + "&quantity=" + encodeURIComponent(quantity)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // Thực hiện hành động nào đó nếu cần
                } else if (data.status === "error" && data.message === 'You must be logged in to add items to the cart') {
                    alert("You need to log in to add items to the cart. Redirecting to login page...");
                    window.location.href = "login.php"; // Đổi đường dẫn nếu cần
                } else {
                    console.error("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        });
    });
});
