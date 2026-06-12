## ADDED Requirements

### Requirement: Receipt list page
The system SHALL display an authenticated user's receipts in a table sorted by `created_at` descending, showing status (French label + colored badge) and depense count per receipt.

#### Scenario: User views empty receipt list
- **WHEN** an authenticated user navigates to `/recus`
- **THEN** the page displays "Aucun reçu" with a link to create one

#### Scenario: User views receipt list with receipts
- **WHEN** an authenticated user has receipts and navigates to `/recus`
- **THEN** each receipt row shows: statut badge (colored), depense count badge, "Voir" link, and "Supprimer" button
- **THEN** receipts are ordered by most recent first

#### Scenario: Receipt list must be authenticated
- **WHEN** an unauthenticated user navigates to `/recus`
- **THEN** they are redirected to the login page

### Requirement: Receipt creation
The system SHALL allow authenticated users to submit receipt text via a form. On submission, the receipt is saved with `pending` status and an `ExtraireDepensesDuRecu` job is dispatched.

#### Scenario: User creates a receipt
- **WHEN** an authenticated user submits valid receipt text to `POST /recus`
- **THEN** a new `Recu` is created with `statut = pending` and belongs to the authenticated user
- **THEN** an `ExtraireDepensesDuRecu` job is dispatched for the new receipt
- **THEN** the user is redirected to `/recus` with a success flash message "Reçu créé avec succès"

#### Scenario: Receipt creation with short text fails
- **WHEN** an authenticated user submits receipt text with fewer than 10 characters
- **THEN** validation fails with an error on `texte_source`
- **THEN** no receipt is created and no job is dispatched

#### Scenario: Receipt creation with long text fails
- **WHEN** an authenticated user submits receipt text exceeding 10000 characters
- **THEN** validation fails with an error on `texte_source`
- **THEN** no receipt is created and no job is dispatched

#### Scenario: Receipt creation must be authenticated
- **WHEN** an unauthenticated user submits to `POST /recus`
- **THEN** they are redirected to the login page

### Requirement: Receipt show page
The system SHALL display a single receipt with its full `texte_source`, status (French label), and a table of extracted depenses (libelle, quantite, prix_unitaire, categorie). The user MUST own the receipt to view it.

#### Scenario: User views their receipt
- **WHEN** an authenticated user navigates to `/recus/{id}` where they own the receipt
- **THEN** the page shows: the full `texte_source`, the `statut` badge, and a depenses table
- **THEN** each depense row shows: libelle, quantite, prix_unitaire formatted with 2 decimals and "MAD", categorie with French label

#### Scenario: User cannot view another user's receipt
- **WHEN** an authenticated user navigates to `/recus/{id}` where another user owns the receipt
- **THEN** a 404 response is returned

#### Scenario: Receipt show must be authenticated
- **WHEN** an unauthenticated user navigates to `/recus/{id}`
- **THEN** they are redirected to the login page

### Requirement: Receipt deletion
The system SHALL allow authenticated users to delete their own receipts. Deleting a receipt MUST cascade-delete all associated depenses.

#### Scenario: User deletes their receipt
- **WHEN** an authenticated user submits `DELETE /recus/{id}` where they own the receipt
- **THEN** the receipt and all its depenses are deleted from the database
- **THEN** the user is redirected to `/recus` with a success flash message "Reçu supprimé avec succès"

#### Scenario: User cannot delete another user's receipt
- **WHEN** an authenticated user submits `DELETE /recus/{id}` where another user owns the receipt
- **THEN** a 404 response is returned

#### Scenario: Receipt deletion must be authenticated
- **WHEN** an unauthenticated user submits `DELETE /recus/{id}`
- **THEN** they are redirected to the login page

### Requirement: New receipt form page
The system SHALL provide a `GET /recus/create` page with a form to submit receipt text.

#### Scenario: User sees the creation form
- **WHEN** an authenticated user navigates to `/recus/create`
- **THEN** the page displays a textarea for `texte_source`, a submit button labeled "Analyser le reçu", and a note about minimum/maximum length
- **THEN** the page also shows validation errors if redirected back with errors

### Requirement: StoreRecuRequest validation
The system SHALL use a `StoreRecuRequest` FormRequest class to validate receipt submission.

#### Scenario: Validation rules
- **WHEN** `StoreRecuRequest` validates the input
- **THEN** `texte_source` MUST be: required, string, min:10 characters, max:10000 characters
