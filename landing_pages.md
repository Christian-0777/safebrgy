# SafeBrgy Landing Pages Discussion

## 1. Introduction

SafeBrgy provides two separate landing experiences for Barangay San Jose, San Luis, Pampanga:

1. The **resident landing page**, served by `index.php`, introduces the barangay, explains the available services, presents the barangay officials, and gives residents a direct way to register or log in.
2. The **admin landing page**, served by `admin_index.php`, acts as a public-facing entrance to the barangay management system and directs authorized personnel to the administrator login page.

The two pages share the SafeBrgy identity, the Barangay San Jose seal, a blue-based color scheme, responsive navigation, and the same general goal of making barangay services easier to access. Their content and calls to action are intentionally different. The resident page is informational and service-oriented, while the admin page is concise and access-oriented.

## 2. Resident Landing Page

### 2.1 Purpose and target users

The resident landing page is designed for current and prospective residents of Barangay San Jose. It serves as the first point of contact for people who want to understand the SafeBrgy portal, request barangay documents, create an account, or access an existing account.

The page is publicly accessible. Residents can read the information and view the service catalogue without logging in. Actions that involve resident-specific transactions are protected by the login flow.

### 2.2 Content

#### Header and primary navigation

The page header displays the Barangay San Jose seal beside the barangay name. The logo links back to the homepage and provides immediate visual identification of the organization.

The desktop navigation contains the following anchor links:

- **Home**: Returns to the hero section.
- **About**: Explains the purpose of SafeBrgy and shows the registration guide.
- **Services**: Displays the available barangay services.
- **Officials**: Lists the officials of Barangay San Jose.
- **Register**: Moves to the resident account registration form.
- **Contact**: Shows the barangay location and contact information.

The header also contains a **Login** button that leads to `public/login.php`. On smaller screens, the desktop navigation is replaced by a mobile navigation menu opened through the menu toggle.

#### Hero section

The hero section is the first visual area of the page. It presents the SafeBrgy name and the main message:

> Welcome to the SafeBrgy — a modern solution built to bring essential barangay services right to your fingertips.

The section uses `assets/img/hero.jpg` as a full-width background image. A dark overlay is placed over the image so the white heading and supporting text remain readable. The barangay seal is displayed beside the text in a circular presentation, reinforcing the official identity of the portal.

The hero does not contain a separate registration form or service search field. Its primary function is orientation and branding, while the header and the sections below provide the actual navigation and transactions.

#### About section

The About section explains the system's mission: empowering the community through fast and reliable barangay services supported by digital innovation.

It also contains a three-step registration guide:

1. The resident clicks the Register link at the top of the homepage.
2. The resident completes and submits the account creation form.
3. The resident waits for administrator verification before logging in.

This guide sets the correct expectation that registration is not immediately active. An administrator must review and approve the resident account first.

#### Services section

The Services section introduces six available service types. Each service is displayed as an individual card containing an icon, a title, a short explanation, and a **Request now** button.

The services are:

- **Barangay Clearance**: Proof that a person has no bad record in the barangay.
- **Barangay Residency**: Confirmation that a person resides in the barangay.
- **Barangay Indigency**: A document for low-income residents who may need aid, scholarships, or medical assistance.
- **Business Clearance**: Permission for a business to operate within the barangay.
- **Incident Report**: A record of a complaint or incident filed at the barangay.
- **Lost Property**: Assistance for residents who have misplaced or lost belongings.

The service list is generated from a PHP array, which keeps the title, description, and Material icon associated in one place. This makes the catalogue easier to maintain than manually repeating the same structure for every card.

#### Officials section

The Officials section provides a public list of Barangay San Jose officials and their positions. The names are arranged in two columns on larger screens and one column on smaller screens.

The list includes the Barangay Captain, Kagawads, Secretary, Treasurer, SK Chairperson, and SK Kagawads. Each row separates the official's name from the corresponding role, allowing visitors to scan the list quickly.

#### Registration section

The registration section allows a new resident to create a SafeBrgy account. It clearly states that administrator approval is required before the account can be used to log in.

The form collects a comprehensive resident profile, including:

- First, middle, and last name
- Birthdate, age, and place of birth
- Gender and civil status
- Nationality and religion
- Complete address and Purok
- Years of residency
- Mobile number and email address
- Voter status
- Employment status and occupation
- Household head and emergency contact
- Number of family members
- Educational attainment
- Blood type and optional disabilities information
- Valid ID upload and profile image upload
- Password and password confirmation
- Agreement to the Terms and Condition

The form uses HTML required fields for basic client-side completion checks. It submits to `register.php` using `POST` and `multipart/form-data`, which is necessary because the form includes document and image uploads. Server-side registration errors are read from the session and displayed as an alert list when the user returns to the page.

#### Contact section and footer

The Contact section identifies the barangay location as Sitio Manena, Barangay San Jose, San Luis, Pampanga, Philippines.

The footer repeats the Barangay San Jose seal and provides grouped links for useful page sections, services, and contact details. It also displays the current year dynamically through PHP and includes an all-rights-reserved notice.

### 2.3 Layout

The resident page uses a single-page, vertical layout. Visitors move through the experience by scrolling or selecting anchor links in the sticky header.

The layout order is:

1. Sticky header
2. Hero banner
3. About section
4. Services grid
5. Barangay officials list
6. Resident registration form
7. Contact section
8. Footer

The content is constrained by a centered container that occupies approximately 92 percent of the viewport, with a maximum width of 1,200 pixels. This prevents long text and large grids from becoming difficult to read on wide displays.

The services use a three-column grid on desktop, two columns at medium widths, and a single-column flow on narrow screens. The officials use two columns on desktop and collapse to one column on smaller screens. The registration form uses flexible rows that wrap when there is no longer enough horizontal space.

### 2.4 Visual design

The resident page uses a civic and service-oriented visual language:

- **Primary colors**: Bright blue and dark blue communicate trust, public service, and institutional identity.
- **Accent color**: Orange is used for service icons and request buttons so the main action stands out from the blue navigation and branding.
- **Backgrounds**: Light blue and white backgrounds keep the long page readable and separate the sections without making the interface visually heavy.
- **Typography**: The page uses a clean sans-serif system font stack with strong headings, muted supporting text, and comfortable line height.
- **Cards**: Service cards and official-list containers use white surfaces, light borders, rounded corners, and soft shadows.
- **Hero treatment**: The photographic background, dark overlay, white text, and seal create a strong first impression while preserving text contrast.
- **Buttons**: Login buttons use a blue gradient; service and registration actions use an orange gradient. The rounded shape makes the actions visually distinct from ordinary navigation links.

Service cards have a small hover elevation effect. This gives feedback when a visitor explores the available services without changing the page structure.

### 2.5 Functions and interactions

The resident landing page provides the following functions:

- Smooth scrolling between page sections through anchor links.
- Sticky header navigation that remains available while the user scrolls.
- Responsive mobile navigation opened by a menu button.
- Logo navigation back to the resident homepage.
- Direct navigation to the resident login page.
- Public viewing of available services and barangay officials.
- Login protection for service requests.
- New resident account registration.
- Upload of a valid ID and profile image during registration.
- Display of registration validation errors through session-based messages.
- Dynamic display of the current year in the footer.

The **Request now** buttons do not submit a service request directly from the public landing page. The login modal script intercepts these buttons and displays the message that the resident must log in first. The modal provides a link to the resident login page and can be closed by selecting the close control or clicking outside the modal. The **Create Account** button is inside the registration form, so it is allowed to submit normally rather than opening the login modal.

After login, residents use the authenticated pages for the full request workflow, request history, announcements, reports, and account management. The landing page therefore acts as the public introduction and access point, not as the complete transaction workspace.

## 3. Admin Landing Page

### 3.1 Purpose and target users

The admin landing page is intended for barangay administrators and authorized staff. Its purpose is to establish the administrative identity of SafeBrgy and provide a clear route to the protected admin login page.

Unlike the resident page, it does not ask administrators to browse public information or fill out an account registration form. It presents a focused entry point to the management system.

### 3.2 Content

#### Header and navigation

The admin header contains the Barangay San Jose seal and the label **Brgy San Jose**. Selecting the brand returns the user to `index.php`, connecting the administrative entrance with the public resident homepage.

The desktop navigation presents four management areas:

- **Dashboard**
- **Residents**
- **Reports**
- **Announcements**

These links are currently section anchors on the landing page. They communicate the major responsibilities of the admin system, but the landing page itself does not render dashboard, resident, report, or announcement content.

On mobile, the navigation is moved into a collapsible menu. The mobile menu additionally includes an explicit **Admin Login** link.

#### Hero section

The main admin hero displays the title **Efficient Barangay Management System**, the location **Barangay San Jose, San Luis, Pampanga**, and an **Admin Login** button.

The hero uses the same `hero.jpg` asset as the resident page, with a dark overlay and centered white text. This keeps the two landing pages visually related while the title and action make the administrative audience clear.

The admin hero is intentionally minimal. It does not expose resident information, administrative statistics, or operational controls before authentication.

#### Footer

The admin footer contains the label **Barangay San Jose Admin**, links for Terms of Service, Privacy Policy, and Contact Support, and a dynamically generated copyright year.

The footer links are currently placeholders using `#`. They establish the intended support and legal-information areas, but they do not yet navigate to dedicated documents from this landing page.

### 3.3 Layout

The admin page uses a compact two-part layout:

1. Sticky administrative header with branding and navigation.
2. Large hero area followed immediately by the footer.

The hero has a minimum height of 815 pixels and uses a centered content block. Its overlay adds generous vertical padding, producing a prominent but simple entry screen. The footer uses a horizontal two-column arrangement on wider screens and stacks its content vertically at widths below 900 pixels.

The desktop navigation and header login action are hidden at smaller widths. They are replaced by a circular navigation toggle and a mobile menu so the page remains usable on phones and tablets.

### 3.4 Visual design

The admin page uses a more restrained management-oriented style:

- **Brand palette**: Blue and dark blue provide a professional administrative identity.
- **Accent**: Orange is defined in the stylesheet for consistency with the resident page, although the admin landing screen primarily emphasizes blue.
- **Header**: A nearly opaque white sticky header, light border, and soft shadow keep navigation readable over the page.
- **Brand mark**: The seal is displayed in a circular, lightly tinted container.
- **Hero**: A full-bleed photograph with a dark translucent overlay creates contrast for the centered title and login action.
- **Login action**: The button uses a blue-to-dark-blue gradient and rounded corners to make the protected entry point obvious.
- **Footer**: A blue gradient footer provides a strong visual endpoint and separates legal/support links from the hero.

The design avoids exposing internal administrative data in public view. The visual hierarchy directs the user from the system title to the single meaningful action: administrator login.

### 3.5 Functions and interactions

The admin landing page provides the following functions:

- Navigation back to the resident homepage through the brand link.
- Anchor navigation labels for the main administrative areas.
- Direct access to `admin/login.php` through the hero login button.
- Mobile navigation toggle behavior.
- Closing the mobile navigation when the user clicks elsewhere on the page.
- Dynamic copyright year generation through PHP.
- Shared logo-click handling through `logo_functions.js`.

The mobile menu is controlled by `admin_landing.js`. Clicking the menu button toggles the `open` class on the mobile navigation. A document-level click handler closes the menu when the click occurs outside both the menu and the toggle button.

The actual administrative functions are handled after authentication by the admin pages. Those functions include dashboard monitoring, resident verification, request processing, reports, announcements, notifications, profiles, and account settings. The landing page's responsibility is to introduce and protect the entry to those functions.

## 4. Comparison of the Two Landing Pages

| Area | Resident landing page | Admin landing page |
|---|---|---|
| Main audience | Residents and public visitors | Barangay administrators and staff |
| Main purpose | Explain services and begin resident workflows | Present the management system and route staff to login |
| Page depth | Long single-page content experience | Short hero-and-footer gateway |
| Primary action | Login, register, or request a service after login | Admin Login |
| Public content | Mission, services, officials, registration, contact | System title, location, management areas, support links |
| Form | Comprehensive resident registration form | No form on the landing page |
| Service access | Public service catalogue with login protection | No public operational data |
| Navigation model | Multiple anchors across the same page | Management-area anchors plus login route |
| Responsive behavior | Collapsible mobile navigation, wrapping grids and form rows | Collapsible mobile navigation and stacked footer |

The difference in scope is appropriate. Residents need context before deciding to register or request a document, while administrators generally need a secure and direct path to the management workspace.

## 5. Shared Technical and Usability Considerations

Both pages use semantic HTML sections, responsive viewport settings, a centered content container, and a sticky header. Both use the barangay seal and `hero.jpg` to maintain brand continuity. Their JavaScript is lightweight and focused on navigation and access control rather than replacing server-side security.

The resident page uses Bootstrap, Font Awesome, and Material Icons in addition to the project's custom CSS. The admin landing page uses its dedicated stylesheet and a small JavaScript file. This keeps the admin gateway independent from the larger resident page styling while preserving the same brand direction.

The login modal on the resident page improves the public user experience by explaining why a service cannot be requested anonymously. However, the modal is only a user-interface convenience; authorization must still be enforced by the authenticated service endpoints and resident pages. Similarly, the registration form's HTML `required` attributes improve usability but do not replace server-side validation of values, files, passwords, consent, and duplicate accounts.

## 6. Current Scope and Possible Improvements

The current landing pages successfully establish separate resident and admin entry points. The following implementation details should be considered in future refinement:

- Connect the admin navigation labels to the corresponding authenticated admin routes if they are intended to be functional links from the landing page.
- Replace the admin footer placeholder links with the actual Terms of Service, Privacy Policy, and support destinations.
- Add explicit accessible labels and keyboard behavior for the resident modal close control and the navigation toggles.
- Add visible focus states to links, buttons, and form controls for keyboard users.
- Consider moving registration form inline styles into the project stylesheet for easier maintenance and more consistent responsive behavior.
- Keep the service descriptions, official list, and contact information synchronized with authoritative barangay records.
- Confirm that the hero image has appropriate loading, contrast, and alternative treatment if it fails to load.

Overall, the resident landing page functions as a complete public information and registration portal, while the admin landing page functions as a branded security gateway into the administrative system. Together they provide a clear separation between public resident services and protected barangay management operations.
