
# Afghan Antiques Store Management System
## Product Backlog

**Product Owner:** Khadija Khalil  
**Scrum Master:** Mahdia Noori  
**Development Team:** Shokrya Barak Insaf (Backend), Zohra Khawaja (Frontend)  
**Course:** Software Engineering  
**Methodology:** Scrum  
**Date:** October 2026

---

## 1. Introduction

The Product Backlog is the main document containing all functional and non-functional requirements for the Afghan Antiques Store Management System.

The purpose of this project is to develop an online platform where customers can browse Afghan antiques, register and log in, save favorite products, manage shopping carts, and submit orders. Store administrators can manage products, customer information, and order activities.

The Product Backlog contains 20 prioritized user stories. Each user story includes acceptance criteria, priority, estimated effort, sprint assignment, and completion status.

The project was organized into three sprints.

### Current Project Status

Based on the team's latest progress:

- **1 user story (US15)** remains blocked because payment-status functionality has not been implemented.
- **2 user stories (US18 and US20)** were deferred because they were not necessary for the current project scope.

The eight non-functional requirements were reviewed and approved by the Scrum Master and development team. Their implementation and testing are evaluated separately from the functional user stories.

Incomplete and deferred stories remain in the Product Backlog for possible future development.

---

## 2. Functional Backlog Items (User Stories)

| ID | User Story | Acceptance Criteria | Priority | Estimation | Sprint | Status |
|---|---|---|---|---|---|---|
| US01 | As a customer, I want to register an account using my information so that I can place orders. | Valid information is accepted; an account is created; duplicate email addresses are rejected. | High | 5 | Sprint 1 | Done |
| US02 | As a customer, I want to log in securely with my credentials so that I can access my account. | Correct credentials allow login; incorrect credentials are rejected; authenticated sessions are maintained. | High | 3 | Sprint 1 | Done |
| US03 | As a customer, I want to log out of my account so that my personal information stays protected. | Logout ends the active session; protected pages require authentication. | High | 2 | Sprint 1 | Done |
| US04 | As a customer, I want to browse available Afghan antique products so that I can explore items for sale. | Available products are displayed with their names, images, and basic information. | High | 5 | Sprint 2 | Done |
| US05 | As a customer, I want to view product photos, descriptions, and prices so that I can decide what to purchase. | Product pages display images, descriptions, prices, and availability. | High | 3 | Sprint 2 | Done |
| US06 | As a customer, I want to search for products by name so that I can quickly find an antique I am interested in. | Search displays matching products; a message appears when no matches are found. | High | 5 | Sprint 2 | Done |
| US07 | As a customer, I want to add products to my shopping cart so that I can collect items before checkout. | Available products can be added to the cart; selected items appear correctly. | High | 5 | Sprint 3 | Done |
| US08 | As a customer, I want to change quantities or remove items from my cart so that I can review my purchases before ordering. | Quantities can be updated; items can be removed; totals update correctly. | High | 5 | Sprint 3 | Done |
| US09 | As a customer, I want to submit my order and receive confirmation so that I know my purchase request was received. | Valid orders are recorded; confirmation is displayed; order information is saved. | High | 8 | Sprint 3 | Done |
| US10 | As a customer, I want to provide a delivery address so that my order can be sent to the correct location. | Required address information is validated and associated with the correct order. | High | 3 | Sprint 2 | Done |
| US11 | As a store administrator, I want to update product details so that listings remain accurate and current. | Authorized administrators can edit product information; changes are saved and displayed. | High | 5 | Sprint 2 | Done |
| US12 | As a store administrator, I want to remove products that are no longer sold so that customers only see available items. | Administrators can remove or deactivate products; unavailable products cannot be purchased. | Medium | 3 | Sprint 3 | Done |
| US13 | As a store administrator, I want to update order progress so that customers and staff can track the order status. | Administrators can update order progress; the latest status is saved and displayed. | Medium | 5 | Sprint 3 | Done |
| US14 | As a customer, I want to view my previous orders so that I can review my purchase history. | Customers can view their own previous orders and order details. | Medium | 5 | Sprint 3 | Done |
| US15 | As a store administrator, I want to mark orders as paid or unpaid so that payment status can be tracked. | Administrators can mark orders as paid or unpaid; changes are saved correctly. | Low | 3 | Sprint 3 | Blocked |
| US16 | As a store administrator, I want to view customer information securely so that I can support order management. | Authorized administrators can view relevant customer information; unauthorized access is restricted. | Medium | 5 | Sprint 3 | Done |
| US17 | As a store administrator, I want to view a summary of sales so that I can understand store performance. | A sales summary is displayed using the store's recorded sales information. | Low | 5 | Sprint 3 | Done |
| US18 | As a customer, I want to request a return under the store return policy so that I can ask to return an unsatisfactory product. | Customers can submit return requests for eligible purchases; requests are recorded for review. | Low | 5 | Sprint 3 | Deferred |
| US19 | As a customer, I want to save favorite products to a wishlist so that I can revisit them and purchase them later. | Customers can add and remove wishlist items; saved items remain associated with their accounts. | Low | 5 | Sprint 2 | Done |
| US20 | As a store administrator, I want to create special offers so that I can promote selected products to customers. | Administrators can create promotional offers and display them for eligible products. | Low | 5 | Sprint 3 | Deferred |

**Estimation Note:** Story points are suggested planning estimates and should be confirmed by the development team.

---

## 3. Enhancement Tasks

Enhancement tasks represent optional improvements that may be considered in future development.

| ID | Enhancement | Acceptance Criteria | Priority | Estimation | Sprint |
|---|---|---|---|---|---|
| ENHANCE-001 | Improve product image presentation. | Product images are displayed clearly and consistently. | Medium | 3 | Future |
| ENHANCE-002 | Improve shopping cart usability. | Customers can easily understand product quantities, prices, and cart totals. | Medium | 3 | Future |
| ENHANCE-003 | Improve mobile responsiveness. | Main website pages and controls are usable on smaller screens. | Medium | 5 | Future |

These enhancements are proposed future improvements and are not included in the completed user-story count.

---

## 4. Non-Functional Backlog Items

The project's requirements specification is in line with the following non-functional criteria.

The Product Owner verified that these criteria were examined and approved by the Scrum Master and development team.

A requirement's approval does not guaranty that its implementation has passed technical testing.

| ID | Non-Functional Requirement | Acceptance / Verification Criteria | Priority | Estimation |
|---|---|---|---|---|
| NFR01 | Security: Passwords shall be stored as secure password hashes, not plaintext. | Database inspection confirms that passwords are not stored as plaintext. | High | 5 |
| NFR02 | Access Control: Administrative product, order, and customer-management functions shall be restricted to authorized staff. | Unauthorized users cannot access protected administrative functions. | High | 5 |
| NFR03 | Input Validation: Registration, login, order, and product forms shall validate required fields and reject invalid input. | Invalid and missing information is rejected with appropriate feedback. | High | 5 |
| NFR04 | Usability: Customers shall be able to navigate product browsing, cart, and order pages with clear labels and feedback. | Main navigation and actions are understandable and provide clear feedback. | High | 3 |
| NFR05 | Compatibility: The interface shall work on the desktop and mobile browser sizes agreed by the team. | The interface is tested at the screen sizes selected by the team. | Medium | 5 |
| NFR06 | Performance: The team shall define an achievable response-time target for key pages and test it locally. | A response-time target is agreed upon and measured during local testing. | Medium | 5 |
| NFR07 | Reliability: The system shall show understandable error messages when a request fails and avoid duplicate order submissions where feasible. | Failed requests display appropriate messages; duplicate submission handling is tested. | High | 5 |
| NFR08 | Data Integrity: Product, customer, and order data shall be stored consistently in the chosen MySQL database, using appropriate relationships and constraints. | Database records and relationships are checked for consistency. | High | 5 |

**Review Status:** Reviewed and approved by the Scrum Master and development team.


**Estimation Note:** Non-functional story-point estimates are proposed values.

---

## 5. Prioritization Strategy

The Product Owner prioritizes user stories according to business value, customer needs, technical dependencies, and project scope.

### High Priority

High-priority items represent the core functionality needed for the online store.

These include:

- Customer registration, login, and logout
- Product browsing and product details
- Product searching
- Shopping cart functionality
- Order submission and confirmation
- Delivery address management
- Product information updates
- Security and access control

### Medium Priority

The client experience and retail management are enhanced by medium-priority items.

These include:

- Removing unavailable products
- Updating order progress
- Viewing order history
- Secure customer information access
- Usability and system compatibility

### Low Priority

Low-priority items provide additional business features.

These include:

- Payment status tracking
- Sales summary
- Product returns
- Wishlists
- Special offers

Some low-priority features were deferred because they were not necessary for the current project scope.

---

## 6. Guidance on Sprint Planning

### Sprint 1: Foundation and Customer Accounts

**Dates:** October 18–21, 2026

**Sprint Goal:** Establish the foundation of the system and implement customer account functionality.

**User Stories:**

- US01 — Customer Registration
- US02 — Customer Login
- US03 — Customer Logout

**Sprint Outcome:**

All three user stories were completed and tested.

Customers can create accounts, log in, and log out securely.

**Sprint 1 Summary:**
- Planned User Stories: 3
- Completed: 3
- Blocked: 0
- Deferred: 0

---

### Sprint 2: Product Discovery and Store Features

**Dates:** October 22–25, 2026

**Sprint Goal:** Implement product discovery and selected customer and administrator features.

**User Stories:**

- US04 — Browse Antique Products
- US05 — View Product Details
- US06 — Search Products
- US10 — Provide Delivery Address
- US11 — Update Product Details
- US19 — Save Products to Wishlist

**Sprint Outcome:**

All six user stories were reported as completed and tested.

Customers can browse and search antique products, view product details, provide delivery addresses, and save favorite products.

Administrators can update product information.

**Sprint 2 Summary:**
- Planned User Stories: 6
- Completed: 6
- Blocked: 0
- Deferred: 0

---

### Sprint 3: Shopping Cart, Orders, and Administration

**Sprint Goal:** Complete shopping cart functionality, order management, and remaining administrative features.

**User Stories:**

- US07 — Add Products to Cart
- US08 — Update or Remove Cart Items
- US09 — Submit Order
- US12 — Remove Unavailable Products
- US13 — Update Order Progress
- US14 — View Order History
- US15 — Manage Payment Status
- US16 — View Customer Information
- US17 — View Sales Summary
- US18 — Request Product Return
- US20 — Create Special Offers

**Sprint Outcome:**

We finished and tested eight user stories. Two low-priority stories were postponed, while one article is still blocked.

Shopping cart administration, order submission, order tracking, purchase history, customer information access, and sales summaries are all supported by the finished features.
### Blocked User Story

**US15 — Manage Payment Status**

**Status:** Blocked

**Reason:** PThe payment feature was not finished, so the admin cannot mark orders as paid or unpaid yet.
**Next Step:** Implement payment-status recording and test administrative updates before marking the story Done.

### Deferred User Stories

**US18 — Request Product Return**

**Status:** Deferred — Not Necessary for Current Scope

**Reason:** The current system does not include a complete online payment and refund workflow. The team therefore deferred the return feature and prioritized essential shopping and order functionality.

**Future Consideration:** Implement return requests and return eligibility rules when payment and refund support are added.

**US20 — Create Special Offers**

**Status:** Deferred — Not Necessary for Current Scope

**Reason:** Promotional offers were considered an optional feature rather than an essential requirement for the current version. The team prioritized core shopping, product, and order management functionality.

**Future Consideration:** Add promotional offers and discount management in a future release.

**Sprint 3 Summary:**
- Planned User Stories: 11
- Completed: 8
- Blocked: 1
- Deferred: 2

---

## 7. Product Backlog Refinement

The Product Owner keeps the Product Backlog updated during the project.

The Product Owner works with the Scrum Master and developers to review requirements, clarify user stories, and adjust priorities when necessary.

Backlog refinement includes:

- Reviewing and clarifying user stories
- Updating acceptance criteria
- Reprioritizing items according to business value
- Identifying technical dependencies
- Reviewing sprint assignments
- Identifying blocked or deferred work
- Adding enhancement tasks when needed

### Backlog Refinement Decisions

During project development, the team identified payment-related dependencies and optional features that were not essential for the current release.

As a result:

- US15 remains blocked until payment-status functionality is implemented and tested.
- US18 was deferred because the return and refund workflow is outside the current release scope.
- US20 was deferred because promotional offers are optional for the current release.

The Product Owner retains these stories in the Product Backlog for possible future implementation.

---

## 8. Definition of Done

A user story is considered Done when:

1. The required functionality has been implemented.
2. All acceptance criteria have been satisfied.
3. The feature has been tested successfully.
4. Major bugs have been corrected.
5. The feature has been integrated into the working system.
6. The code has been reviewed as required by the team.
7. The changes have been committed to GitHub.
8. Relevant documentation has been updated.
9. The Product Owner has reviewed and accepted the completed feature.

A blocked or deferred story is not considered Done.

---

## 9. Final Product Backlog Summary

| Sprint | Total Stories | Done | Blocked | Deferred |
|---|---:|---:|---:|---:|
| Sprint 1 | 3 | 3 | 0 | 0 |
| Sprint 2 | 6 | 6 | 0 | 0 |
| Sprint 3 | 11 | 8 | 1 | 2 |
| **Total** | **20** | **17** | **1** | **2** |

### Final Outcome

The Afghan Antiques Store Management System has 17 completed and tested user stories out of 20.

One user story remains blocked, and two low-priority user stories have been deferred for possible future development.

The project's non-functional requirements have been reviewed and approved by the Scrum Master and development team.

The Product Backlog documents completed work, outstanding requirements, and future improvements.

---

**Prepared by:** Khadija Khalil  
**Role:** Product Owner  
**Project:** Afghan Antiques Store Management System  
**Course:** Software Engineering
