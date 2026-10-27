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

- ## Functional Requirements (FR)

The following functional requirements are based on the 20 prioritized user stories prepared by the Product Owner. They describe what the Afghan Antiques Store Management System should allow customers and administrators to do.

### Customer Account Management

**FR01 – Customer Registration (US01)**  
The system shall allow customers to create an account by providing their registration information.

**FR02 – Customer Login (US02)**  
The system shall allow registered customers to log in securely using their credentials.

**FR03 – Customer Logout (US03)**  
The system shall allow customers to log out of their accounts securely.

### Product Browsing and Shopping

**FR04 – Browse Products (US04)**  
The system shall allow customers to browse available Afghan antique products.

**FR05 – View Product Details (US05)**  
The system shall display product photos, descriptions, and prices so customers can review items before purchasing.

**FR06 – Search Products (US06)**  
The system shall allow customers to search for antique products by name.

**FR07 – Add Products to Cart (US07)**  
The system shall allow customers to add products to their shopping cart.

**FR08 – Manage Shopping Cart (US08)**  
The system shall allow customers to change product quantities or remove items from their shopping cart.

### Orders and Delivery

**FR09 – Submit Orders (US09)**  
The system shall allow customers to submit orders and receive confirmation that their order request was received.

**FR10 – Delivery Address (US10)**  
The system shall allow customers to provide a delivery address for their orders.

### Product and Order Administration

**FR11 – Update Product Details (US11)**  
The system shall allow store administrators to update product information.

**FR12 – Remove Products (US12)**  
The system shall allow store administrators to remove products that are no longer available for sale.

**FR13 – Update Order Status (US13)**  
The system shall allow store administrators to update the progress or status of customer orders.

**FR14 – View Order History (US14)**  
The system shall allow customers to view their previous orders.

**FR15 – Manage Payment Status (US15)**  
The system shall allow store administrators to mark orders as paid or unpaid.

**FR16 – View Customer Information (US16)**  
The system shall allow authorized store administrators to view customer information needed for order management.

### Additional Store Features

**FR17 – View Sales Summary (US17)**  
The system shall allow store administrators to view a summary of store sales.

**FR18 – Request Product Returns (US18)**  
The system shall allow customers to request a product return according to the store's return policy.

**FR19 – Wishlist (US19)**  
The system shall allow customers to save favorite products to a wishlist for future reference.

**FR20 – Special Offers (US20)**  
The system shall allow store administrators to create special offers for selected products.

---

## Non-Functional Requirements (NFR)

The following are proposed quality requirements for the Afghan Antiques Store Management System. They describe how the website should operate. The development team will review and confirm these requirements before final approval.

**NFR01 – Security**  
The system shall protect customer information. Passwords shall be stored using secure password hashing, and database queries shall use prepared statements.

**NFR02 – Usability**  
The website shall have a simple, understandable interface so customers can easily navigate pages, find products, and use the shopping cart.

**NFR03 – Performance**  
The website should load pages and respond to normal user actions without unnecessary delays. The development team will agree on measurable performance targets.

**NFR04 – Browser Compatibility**  
The website should work correctly on commonly used modern web browsers, such as Google Chrome, Firefox, and Safari.

**NFR05 – Responsive Design**  
The website shall adjust its layout to support desktop computers, tablets, and mobile phones.

**NFR06 – Reliability**  
The system shall handle invalid inputs and common errors appropriately without unnecessarily losing customer information or order data.

**NFR07 – Data Integrity**  
The system shall validate important information and maintain accurate customer, product, and order records in the MySQL database.

**NFR08 – Maintainability**  
The application code shall be organized and understandable so team members can fix problems and add features more easily.

---

## Requirements Review

**Prepared by:** Khadija Khalil – Product Owner

**Technical Reviewer:** Shokrya Barak Insaf – Backend Developer

**Status:** Draft – Awaiting Team Review

**Note:** These requirements describe the planned system. They do not mean that all features have already been developed. The team will review the requirements and confirm implementation priorities according to the Product Backlog and Sprint Backlogs.

# Created by: 
# Zohra Khwaja 
# Shokrya Barak Insaf
# Khadija Khalil
# Madiha Noori
