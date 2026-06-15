## ADDED Requirements

### Requirement: Expense list page
The system SHALL display all depenses belonging to the authenticated user, across all receipts, in a table sorted by `created_at` descending.

#### Scenario: User views all expenses
- **WHEN** an authenticated user navigates to `/depenses`
- **THEN** the page shows a table with columns: libelle, quantite, prix_unitaire (formatted with "MAD"), categorie (French label), and receipt date
- **THEN** each depense row links to its parent receipt's show page
- **THEN** depenses are ordered by most recent first

#### Scenario: Expense list must be authenticated
- **WHEN** an unauthenticated user navigates to `/depenses`
- **THEN** they are redirected to the login page

#### Scenario: N+1 prevention
- **WHEN** the expense list page loads
- **THEN** the `recu` relationship is eager-loaded and zero additional queries are made per depense row

### Requirement: Category filter
The system SHALL allow users to filter the expense list by category via a `?categorie=` query parameter.

#### Scenario: Filter by valid category
- **WHEN** an authenticated user navigates to `/depenses?categorie=alimentaire`
- **THEN** only depenses with `categorie = alimentaire` are displayed

#### Scenario: Filter by invalid category
- **WHEN** an authenticated user navigates to `/depenses?categorie=invalid`
- **THEN** all depenses are displayed (invalid filter is ignored)

#### Scenario: Filter form in UI
- **WHEN** an authenticated user views the expense list page
- **THEN** a dropdown select with all `DepenseCategorie` options plus "Toutes les catégories" is displayed
- **THEN** selecting a category and submitting reloads the page with the `?categorie=` parameter

### Requirement: Category totals
The system SHALL display aggregated totals (sum of `quantite * prix_unitaire`) per category at the top of the expense list page.

#### Scenario: Category totals display
- **WHEN** an authenticated user navigates to `/depenses`
- **THEN** the page shows a summary section with each category name and its total formatted in MAD
- **THEN** totals reflect the filtered dataset (if a category filter is active, totals adjust accordingly)
