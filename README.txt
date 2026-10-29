AUNING MASKIN — WordPress-tema v1.1.0

INSTALLATION
1. WordPress > Udseende > Temaer > Tilføj nyt tema > Upload tema.
2. Upload auning-maskin-1.1.0.zip og aktivér. Dette erstatter Astra som aktivt tema.
3. Opret en tom side kaldet Forside. Vælg den som statisk forside under Indstillinger > Læsning.
4. Udseende > Tilpas > Auning: forside og kontakt: indtast rigtige kontaktoplysninger og vælg eget forsidefoto.
5. Udseende > Tilpas > Webstedsidentitet: upload virksomhedens logo. ATM-mærket er kun et midlertidigt tekstmærke.
6. Opret en hovedmenu og knyt den til placeringen Hovedmenu. Standardmenuen er kun en enkel reserve, hvor varelinks leder til den samlede butik.
7. Installér/aktivér WooCommerce for produkter og webshop. Kør WooCommerce-opsætning og vælg butiksside. Opret egne kategorier, varer, priser og billeder.

ELEMENTOR
Almindelige sider kan redigeres med Elementor. Hvis forsiden er bygget med Elementor, vises dit Elementor-indhold i stedet for den indbyggede temaforside. Forsidefelterne under Tilpas gælder kun den indbyggede forside. Elementor Canvas bruger ikke temaets header og footer.

INDHOLD
Ingen opdigtede priser, produktfotos, anmeldelser, garantier, åbningstider eller kontaktdata er indsat. Traktorillustrationen er en generisk illustration og kan erstattes under Tilpas. Servicebeskrivelserne er udkast og skal bekræftes af virksomheden. Ingen billeder eller kode er kopieret fra Kalundborg Skov Have Park.

KONTROL FØR LANCERING
Tag backup og afprøv på testmiljø. Kontrollér eksisterende Elementor-sider efter skift fra Astra, mobilmenu, produkter, kurv, checkout, betaling, levering, moms, handelsbetingelser og privatliv. Temaet ændrer ikke eksisterende varer eller sider. Checkout og betaling leveres af WooCommerce og de installerede betalingsplugins.

VALIDERING
Pakke og kildefiler er kontrolleret statisk. Ikke kørt i en WordPress/PHP-installation; PHP, Elementor og WooCommerce skal afprøves på din testside før lancering.

OPDATERINGER FRA GITHUB (1.1.0)
Udseende > Auning opdateringer > Søg efter opdatering. Ved nyere version vises Installér opdatering. Kun brugere med update_themes-rettighed kan kontrollere eller installere.
WordPress' almindelige opdateringstjek understøttes, inklusive den valgfrie automatiske installation. GitHub-resultater gemmes i op til 6 timer; knappen tvinger et nyt tjek. Netværksfejl vises særskilt.
Kilden er den offentlige main-gren i Niklas-Terttu/auning-theme. Hver ny udgivelse kræver et højere Version-felt i style.css. Metadata og ZIP fastlåses til samme commit. Temaets eksisterende mappenavn bevares under opdatering.
Version 1.0.0 skal opdateres manuelt denne ene gang, da den ikke indeholder opdateringskoden. Upload den nye ZIP og vælg at erstatte det eksisterende tema. Brug samme mappenavn som den eksisterende installation. Ved GitHub Download ZIP er mappen typisk auning-theme-main.
Opdateringskode kører kun, mens temaet (eller et child theme baseret på det) er aktivt. Ret ikke direkte i temafilerne, hvis ændringer skal bevares; brug Tilpas eller et child theme.
