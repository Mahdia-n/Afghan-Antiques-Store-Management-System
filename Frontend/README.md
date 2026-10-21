Small Sales and Order Management System
Your project will be a small e-commerce website where customers can browse products, create an account, add items to a shopping cart, and complete a purchase. You will build it in stages, starting with customer management and user registration.
For the first version, the website will have two main parts:
Customer-facing website: A landing page with a “Purchase Now” button, product browsing, sign-up, login, and checkout.
Customer management backend: A database and backend code to register customers, authenticate them, and store their information securely.
Later, you can add inventory management, order tracking, payment integration, and an admin dashboard.

Website initial pages to develop in the first stage:

### Page 1 — Home (index.php)
- Business name and logo.
- Navigation menu.
- Welcome section introducing the antique store.
- “Purchase Now” button linking to products.php.
- Featured antique products.
- Footer with contact information.

### Page 2 — Product Catalog (products.php)
- Display available antique products.
- Show product names, images, descriptions, and prices.
- Include product categories and search functionality.
- Provide Add to Cart and View Cart buttons.
- Use a responsive layout.

### Page 3 — Customer Registration (register.php)
- Collect the customer's full name, email address, password, and password confirmation.
- Validate required fields and email format.
- Check whether the email is already registered.
- Hash passwords using PHP's password_hash().
- Store customer records in MySQL using prepared statements.
- Display appropriate success and error messages.
- Include a link to the login page.

### Page 4 — Shopping Cart (cart.php)
- Display selected products, prices, and quantities.
- Allow customers to update quantities and remove items.
- Calculate the subtotal and total.
- Display an appropriate message when the cart is empty.
- Include a “Continue Shopping” button.
- Provide a “Proceed to Checkout” button.

### Customer Authentication
- Create login and logout functionality.
- Authenticate customers securely using password_verify().
- Use PHP sessions to maintain login status.
- Validate user input on the server.
- Test registration, successful login, invalid credentials, duplicate emails, and logout.
Things to Do:
Design the customer registration form.
Collect the customer's name, email, and password.
Validate required fields and email format.
Check that the email is not already registered.
Hash passwords using PHP's password_hash().
Save customer records to MySQL using prepared statements.
Create the login and logout functionality.
Use PHP sessions to keep customers logged in.
Test registration, login, invalid credentials, and duplicate emails.




