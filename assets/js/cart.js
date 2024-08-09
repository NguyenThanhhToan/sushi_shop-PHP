document.addEventListener("DOMContentLoaded", function() {
    // Function to update the quantity
    function updateQuantity(productId, quantity) {
        fetch("../config_function/update_cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                console.log("Quantity updated!");
                // Reload the page to reflect the changes
                window.location.reload();
            } else {
                console.error("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }

    // Function to remove the product
    function removeProduct(productId) {
        fetch("../config_function/remove_from_cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                console.log("Product removed!");
                // Reload the page to reflect the changes
                window.location.reload();
            } else {
                console.error("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }

    // Function to confirm order
    function confirmOrder() {
        const totalAmount = document.getElementById('hidden-total-amount').value;
        const phone = document.getElementById('phone').value;
        const address = document.getElementById('address').value;

        // Check if phone and address are filled
        if (!phone || !address) {
            alert("Please fill in both phone number and address.");
            return;
        }

        fetch("../config_function/confirm_order.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                total_amount: totalAmount,
                phone: phone,
                address: address
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("Order confirmed successfully!");
                window.location.href = "cart.php"; // Redirect to cart page
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }

    // Event listener for confirm order button
    document.querySelector("#confirm-order").addEventListener("click", function() {
        confirmOrder();
    });

    // Event listeners for quantity buttons
    document.querySelectorAll(".quantity-button").forEach(button => {
        button.addEventListener("click", function() {
            const input = this.parentElement.querySelector(".quantity-input");
            const productId = input.getAttribute("data-product-id");
            let quantity = parseInt(input.value);

            if (this.textContent === "+") {
                quantity += 1;
            } else if (this.textContent === "-") {
                quantity = Math.max(1, quantity - 1); // Ensure quantity doesn't go below 1
            }

            input.value = quantity;
            updateQuantity(productId, quantity);
        });
    });

    // Event listener for delete buttons
    document.querySelectorAll(".delete-button").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.parentElement.querySelector(".quantity-input").getAttribute("data-product-id");
            removeProduct(productId);
        });
    });
});
