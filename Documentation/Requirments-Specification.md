### Project Overview — Antique Store E-Commerce Website
The Antique Store project is a small e-commerce website designed to provide customers with a convenient online platform to explore and purchase antique products. The website will allow customers to browse a collection of antiques, view product details and prices, create an account, securely log in, and add products to a shopping cart.

The project will be developed using PHP, MySQL, HTML, CSS, and JavaScript. PHP will handle server-side functionality, including customer registration, authentication, session management, and database interactions. MySQL will store customer information and, as the project develops, product and order data. HTML, CSS, and JavaScript will be used to create an attractive, responsive, and user-friendly interface.

The initial development stage will focus on customer management and user registration, including account creation, input validation, duplicate email checking, secure password hashing, login, and logout functionality. The customer-facing website will include a home page, product catalog, registration page, login page, and shopping cart.

The design will reflect the character of an antique store through an elegant visual style, consistent navigation, product imagery, and a responsive layout that works across desktop, tablet, and mobile devices. Security will be a priority, with prepared SQL statements, secure password handling, and PHP sessions used to protect customer accounts.

The project will be developed incrementally, allowing additional features such as inventory management, order tracking, payment integration, and an administrative dashboard to be introduced in later stages.

Overall objective: To develop a secure, functional, and visually appealing antique e-commerce website that provides a smooth customer experience while establishing a reliable foundation for future business and order management features.



### Project Stage 1 — Customer Management and User Registration
Purpose: This stage focuses on customer management and user registration for the antique_store e-commerce website. The goal is to create a user-friendly and secure customer experience that allows customers to browse antique products, create accounts, log in and out, and manage their shopping cart.

This stage includes developing the customer-facing frontend and implementing the backend functionality required for customer registration and authentication. Customer information will be stored securely in a MySQL database, passwords will be hashed using PHP's password_hash() function, and prepared statements will be used for database queries.

The website will consist of a home page, product catalog, customer registration page, login page, shopping cart, and logout functionality. All pages will follow a consistent antique-inspired design and provide responsive layouts for desktop, tablet, and mobile devices.

The initial implementation will prioritize customer account management, secure authentication, and the basic shopping experience. Advanced features such as payment integration, order tracking, inventory management, and an administrative dashboard will be developed in later stages.

### Page 1 — Home (index.php)
Purpose: Introduce the antique store, showcase its unique products, and encourage customers to browse and purchase antiques.

Requirements:
- Display the business name and logo.
- Include a navigation menu with links to Home, Products, Login, and Register.
- Create a welcoming hero section introducing the antique store and its offerings.
- Include a prominent “Purchase Now” button linking to products.php.
- Showcase featured antique products with images, names, and prices.
- Include a brief introduction to the store and its collection of antiques.
- Display a footer with store contact information, useful links, and copyright information.
- Ensure the page is responsive on desktop, tablet, and mobile devices.

### Page 2 — Product Catalog (products.php)
Purpose: Allow customers to browse antique products, view product details, and add items to their shopping cart.

Requirements:
- Display available antique products with product names, images, descriptions, and prices.
- Organize products into categories, such as furniture, clocks, ceramics, lighting, and collectibles.
- Provide a search bar to find products by name.
- Include an “Add to Cart” button for each product.
- Display a shopping cart icon with the current item count.
- Include a “View Cart” button linking to cart.php.
- Display a navigation menu with links to Home, Products, Login, and Register.
- Include a footer with store contact information and useful links.
- Ensure the page is responsive on desktop, tablet, and mobile devices.

### Page 3 — Customer Registration (register.php)
Purpose: Allow new customers to create an account securely so they can access customer features and proceed with purchases.

Requirements:
- Design a customer registration form that collects the customer's full name, email address, password, and password confirmation.
- Validate all required fields and ensure the email address follows a valid format.
- Check whether the email address is already registered in the database.
- Verify that the password and confirmation password match.
- Hash passwords securely using PHP's password_hash() function.
- Store customer information in MySQL using prepared statements.
- Display clear success messages and appropriate validation or error messages.
- Include a link to login.php for existing customers.
- Apply consistent branding, typography, colors, and form styling to match the antique store website.
- Ensure the registration page is responsive on desktop, tablet, and mobile devices.

### Page 4 — Shopping Cart (cart.php)
Purpose: Allow customers to review their selected antique products, modify quantities, remove items, and proceed toward checkout.

Requirements:
- Display all selected products with images, names, individual prices, quantities, and line totals.
- Allow customers to increase or decrease product quantities.
- Provide an option to remove products from the cart.
- Automatically calculate the subtotal and total based on the selected products and quantities.
- Display a clear message when the shopping cart is empty.
- Include a “Continue Shopping” button linking to products.php.
- Include a “Proceed to Checkout” button to continue the purchasing process.
- Preserve cart contents as customers navigate between pages, using PHP sessions or another appropriate mechanism.
- Validate quantities and product prices on the server to prevent invalid cart values.
- Include consistent navigation and a footer with store contact information.
- Ensure the page is responsive on desktop, tablet, and mobile devices.

### Page 5 — Customer Login (login.php)
Purpose: Allow registered customers to securely sign in to their accounts and access the website's customer features.

Requirements:
- Design a login form that collects the customer's email address and password.
- Validate required fields and email format.
- Authenticate customers by checking their credentials against the MySQL database.
- Use PHP's password_verify() function to verify passwords securely.
- Display a clear error message when the credentials are invalid without revealing sensitive account information.
- Use PHP sessions to maintain the customer's authenticated state.
- Regenerate the session ID after successful authentication.
- Include a link to register.php for customers who do not have an account.
- Redirect authenticated customers to an appropriate page, such as the home page or their intended destination.
- Apply the same visual design and responsive layout as the registration page.

### Page 6 — Customer Logout (logout.php)
Purpose: Allow authenticated customers to securely end their sessions and log out of their accounts.

Requirements:
- Provide a logout action accessible to authenticated customers through the website's navigation menu.
- Destroy the active PHP session and clear session data appropriately.
- Remove the session cookie when applicable.
- Redirect the customer to the home page or login page after logout.
- Ensure protected customer features are no longer accessible without authentication.
- Display an appropriate message confirming that the customer has logged out, if desired.

### Shared Website Requirements
These requirements apply across the customer-facing pages.
- Use a consistent antique-inspired visual theme with coordinated colors, typography, buttons, and spacing.
- Maintain consistent navigation and footer components across pages.
- Use reusable PHP includes for shared elements such as the header and footer.
- Store database connection settings separately from page presentation code.
- Use prepared statements for database queries.
- Validate user input on both the client side and server side, with server-side validation as the security requirement.
- Protect customer information and never store passwords in plain text.
- Use appropriate session management for authentication and shopping cart functionality.
- Test navigation, form submissions, error handling, cart updates, and responsive

# Created by: 
# Zohra Khwaja 
# Shokrya Barak Insaf
# Khadija Khalil
# Madiha Noori
