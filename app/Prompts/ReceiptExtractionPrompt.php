<?php

namespace App\Prompts;

class ReceiptExtractionPrompt
{
    public function build(): string
    {
        return trim("Tu es un assistant de comptabilité pour une épicerie marocaine.
On te donne le texte brut d'un reçu fournisseur — potentiellement en darija, en français, en arabe, avec des abréviations et des montants mal formatés.

Ta tâche : extraire tous les articles et retourner UNIQUEMENT un objet JSON valide, sans texte avant ni après, sans balises Markdown.

Le JSON doit respecter exactement ce schéma :
{
  \"articles\": [
    {
      \"libellé\": \"string\",
      \"quantité\": \"integer\",
      \"prix_unitaire\": \"number\",
      \"catégorie\": \"alimentaire | boissons | hygiène | entretien | autre\"
    }
  ],
  \"total_estimé\": \"number\",
  \"devise\": \"string\"
}

Règles :
- Si une quantité est manquante, déduis-la à 1.
- Si un prix unitaire est manquant, laisse 0.
- La devise par défaut est MAD.
- Choisis la catégorie la plus proche. En cas de doute, utilise \"autre\".
- Ne génère rien d'autre que le JSON.");
    }
}