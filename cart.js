// 1. Load cart items from localStorage
let cart = JSON.parse(localStorage.getItem('tinas_cart')) || [];

function updateCartDisplay() {
    const tbody = document.querySelector('.cart-table tbody');
    const subtotalVal = document.getElementById('subtotal-val');
    
    if(!tbody) return; 
    
    tbody.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 50px;">Your cart is empty.</td></tr>';
    } else {
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            tbody.innerHTML += `
                <tr>
                    <td>
                        <div class="product-info">
                            <img src="uploads/${item.image}" alt="${item.name}" onerror="this.src='img/placeholder.png'">
                            <span class="product-name">${item.name}</span>
                        </div>
                    </td>
                    <td>₱${item.price.toLocaleString()}</td>
                    <td>
                        <input type="number" value="${item.quantity}" min="1" class="qty-input" onchange="updateQty(${index}, this.value)">
                    </td>
                    <td>₱${itemTotal.toLocaleString()}</td>
                    <td><span class="remove-btn" onclick="removeItem(${index})">REMOVE</span></td>
                </tr>
            `;
        });
    }

    if(subtotalVal) subtotalVal.innerText = `₱${total.toLocaleString()}`;
    
    document.querySelectorAll('.total-price').forEach(el => {
        el.innerText = `₱${total.toLocaleString()}`;
    });
}

window.updateQty = function(index, newQty) {
    if(newQty < 1) newQty = 1;
    cart[index].quantity = parseInt(newQty);
    localStorage.setItem('tinas_cart', JSON.stringify(cart));
    updateCartDisplay();
}

window.removeItem = function(index) {
    if(confirm("Are you sure you want to remove this item?")) {
        cart.splice(index, 1);
        localStorage.setItem('tinas_cart', JSON.stringify(cart));
        updateCartDisplay();
    }
}

window.closeModal = function() {
    document.getElementById('checkoutModal').style.display = 'none';
}

const checkoutBtn = document.querySelector('.checkout-btn');
if(checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
        if(cart.length === 0) {
            alert("Your cart is empty!");
            return;
        }
        document.getElementById('checkoutModal').style.display = 'block';
    });
}

// FIX: Updated placeOrder to match Payment First setup
window.placeOrder = async function(event) {
    event.preventDefault();
    
    const btn = event.target.querySelector('button[type="submit"]');
    if(btn) {
        btn.disabled = true;
        btn.innerText = "PROCESSING...";
    }

    const formData = new FormData();
    
    // Kunin ang values base sa IDs sa cart.php
    formData.append('fullname', document.getElementById('custName').value);
    formData.append('address', document.getElementById('custAddress').value);
    formData.append('phone', document.getElementById('custPhone').value);
    formData.append('payment_method', 'GCash'); // Hardcoded na dahil Payment First
    formData.append('total_amount', cart.reduce((acc, item) => acc + (item.price * item.quantity), 0));
    
    const itemNames = cart.map(item => `${item.name} (x${item.quantity})`).join(", ");
    formData.append('order_items', itemNames);

    // FIX: Match receipt key with PHP
    const fileInput = document.getElementById('receiptFile');
    if (fileInput.files.length === 0) {
        alert("Please upload your GCash receipt first.");
        if(btn) {
            btn.disabled = false;
            btn.innerText = "CONFIRM ORDER & PAY";
        }
        return;
    }
    formData.append('receipt_img', fileInput.files[0]);

    try {
        // Tiyakin ang path ng checkout_process.php
        const response = await fetch('admin/checkout_process.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.text();
        console.log("Server Response:", result); // I-check ang console para sa debug

        if (result.trim().toLowerCase().includes("success")) {
            localStorage.removeItem('tinas_cart');
            document.getElementById('checkoutModal').style.display = 'none';
            document.getElementById('thankYouModal').style.display = 'block';
        } else {
            alert("Order failed: " + result);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Server Error. Make sure 'admin/checkout_process.php' exists.");
    } finally {
        if(btn) {
            btn.disabled = false;
            btn.innerText = "CONFIRM ORDER & PAY";
        }
    }
}

window.closeThankYou = function() {
    window.location.href = 'index.php';
}

updateCartDisplay();