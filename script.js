function shopNow() {
  alert("Welcome to Tina's Jewelry Shop!");
}

/* PARA SA ADD TO CART
*/
function addToCart(name, price, image) {
    let cart = JSON.parse(localStorage.getItem('tinas_cart')) || [];
    let existingItem = cart.find(item => item.name === name);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            name: name,
            price: price,
            image: image, 
            quantity: 1
        });
    }

    localStorage.setItem('tinas_cart', JSON.stringify(cart));
    window.location.href = "cart.php"; 
}

/* PARA SA REGISTRATION
*/
async function handleRegister(event) {
    event.preventDefault();
    let formData = new FormData(event.target);

    let response = await fetch('register.php', {
        method: 'POST',
        body: formData
    });

    let result = await response.text();
    if (result.trim() === "success") {
        alert("Registration Successful! Pwede ka na mag-login.");
        window.location.href = "login.html";
    } else {
        alert(result);
    }
}

/* PARA SA CHECKOUT / PLACE ORDER
 Ito ang solusyon sa triple order issue mo.
*/
async function placeOrder(event) {
    event.preventDefault(); // Pinipigilan ang pag-refresh ng page

    const btn = document.getElementById('placeOrderBtn');
    
    // 1. Disable agad ang button para hindi makailang click ang user
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = "Processing... Please wait.";
    }

    let formData = new FormData(event.target);

    try {
        let response = await fetch('place_order.php', {
            method: 'POST',
            body: formData
        });

        let result = await response.text();
        
        if (result.trim() === "success") {
            alert("Order Placed Successfully!");
            // 2. Importante: Burahin ang cart sa local storage matapos ang order
            localStorage.removeItem('tinas_cart');
            window.location.href = "success.php"; 
        } else {
            alert("Error: " + result);
            // I-enable ulit ang button kung nag-fail para ma-try ulit ng user
            if (btn) {
                btn.disabled = false;
                btn.innerText = "Place Order";
            }
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Something went wrong. Please check your connection.");
        if (btn) {
            btn.disabled = false;
            btn.innerText = "Place Order";
        }
    }
}

