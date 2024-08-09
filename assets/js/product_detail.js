document.addEventListener("DOMContentLoaded", function() {
    const decreaseButton = document.getElementById("decrease-quantity");
    const increaseButton = document.getElementById("increase-quantity");
    const quantityInput = document.getElementById("quantity-input");
    const addToCartButton = document.getElementById("addtocart");

    decreaseButton.addEventListener("click", function() {
        let quantity = parseInt(quantityInput.value);
        quantity = Math.max(1, quantity - 1);
        quantityInput.value = quantity;
    });

    increaseButton.addEventListener("click", function() {
        let quantity = parseInt(quantityInput.value);
        quantityInput.value = quantity + 1;
    });

    addToCartButton.addEventListener("click", function(event) {
        event.preventDefault();
        const quantity = parseInt(quantityInput.value);
        const productId = addToCartButton.dataset.productId;
        const returnUrl = addToCartButton.dataset.returnUrl;
        window.location.href = `product_detail.php?action=add_to_cart&id=${productId}&quantity=${quantity}&return_url=${encodeURIComponent(returnUrl)}`;
    });
});
