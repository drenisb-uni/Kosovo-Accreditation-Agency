# Kosovo Accreditation Agency (AKA) - Management System

Ky është një sistem menaxhimi për Agjencinë e Akreditimit të Kosovës (AKA), i ndërtuar për të lehtësuar procesin e akreditimit institucional dhe akademik. Sistemi lejon administratorët (KSHC) të menaxhojnë dokumentet, të shqyrtojnë kërkesat e reja dhe të monitorojnë statusin e vlefshmërisë së akreditimeve.

## 🚀 Funksionalitetet Kryesore

### Paneli i Administratorit (Admin Dashboard)
- **Menaxhimi i Akreditimeve:** Ngarkimi i dokumenteve PDF dhe caktimi i vlefshmërisë (nga/deri) për institucione dhe fakultete.
- **Shqyrtimi i Kërkesave:** Një seksion i dedikuar për kërkesat e reja që vijnë nga institucionet, me opsione për Aprovim ose Refuzim.
- **Statistikat Live:** Monitorimi në kohë reale i numrit të institucioneve, programeve të aprovuara dhe akreditimeve që skadojnë së shpejti.
- **Sistemi i Raportimit:** Ndërfaqe për menaxhimin e problemeve teknike të raportuara nga përdoruesit.
- **Kërkimi Live:** Filtrues dinamik për të gjetur shpejt universitetet ose statuset specifike në tabelë.

### Paneli i Përdoruesit (User Dashboard)
- **Ngarkimi i Dokumenteve:** Mundësia që institucionet të dërgojnë dokumentacionin për shqyrtim.
- **Raportimi i Problemeve:** Një faqe e dedikuar për të dërguar ankesa ose raporte teknike te admini përmes skedarëve JSON.

## 📂 Struktura e Projektit

Sipas organizimit të dosjeve në këtë depo:

* **`Akreditimet/`**: Dosja kryesore ku ruhen dokumentet PDF dhe skedarët e konfigurimit (`config.txt`) për çdo institucion.
    * `Reports/`: Ruhen raportimet e problemeve në format JSON.
    * `Requests/`: Ruhen kërkesat e reja për akreditim që presin shqyrtimin.
* **`public/`**: Përmban skedarët kryesorë të ballinës dhe ndërfaqes së përdoruesit.
* **`includes/`**: Skedarët ndihmës dhe konfigurimet e sistemit.
* **`classes/`**: Implementimet e hershme të Programimit të Orientuar në Objekte (OOP) për login/logout.
* **`assets/`**: Skedarët statikë (CSS, Imazhe, JS).

## 🛠️ Teknologjitë e Përdorura

- **Backend:** PHP 
- **Frontend:** HTML5, CSS3, JavaScript
- **Storage:** File-based system (PDF, TXT/Config, JSON) - Nuk kërkon bazë të dhënash SQL për ruajtjen e dokumenteve.


## 💻 Hapat për Ekzekutim në XAMPP

Për të ekzekutuar këtë projekt në makinën tuaj lokale, ndiqni këto udhëzime hap pas hapi:

### 1. Shkarkimi i Projektit
Klononi projektin nga GitHub ose shkarkoni skedarin ZIP dhe ekstraptojeni atë në folderin `htdocs` të instalimit tuaj të XAMPP:
`git clone [https://github.com/eldikryeziu04/Kosovo-Accreditation-Agency.git](https://github.com/eldikryeziu04/Kosovo-Accreditation-Agency.git)`
`C:\xampp\htdocs\Kosovo-Accreditation-Agency`

### 2. Aktivizimi i Serverit
- Hapni **XAMPP Control Panel**.
- Startoni modulin **Apache**. (Nuk kërkohet MySQL pasi sistemi bazohet në skedarë JSON dhe TXT).

### 3. Konfigurimi i Permisioneve
Sistemi shkruan dhe fshin skedarë në folderin `Akreditimet/`. Sigurohuni që Apache ka leje për të modifikuar këtë folder:
- Klikoni me të djathtën mbi folderin `Akreditimet`.
- Zgjidhni **Properties** -> **Security**.
- Sigurohuni që përdoruesi `Everyone` ose `Users` ka lejet **Write** dhe **Modify**.

### 4. Struktura e URL-së
Hapni browser-in tuaj dhe shkruani:
`http://localhost/Kosovo-Accreditation-Agency/public/index.php`

### 5. Testimi
Për të hyrë në sistem, përdorni kredencialet e definuara në `authentication.php`:
- **Admin (KSHC):** Përdorni llogarinë e administratorit për të menaxhuar akreditimet dhe raportet.
- **User (Institucioni):** Përdorni llogarinë e përdoruesit për të dërguar kërkesa dhe për të raportuar probleme.

## 🛠️ Troubleshooting (Zgjidhja e Problemeve)

- **Gabimi "File Not Found":** Sigurohuni që emri i folderit në `htdocs` përputhet saktësisht me atë në URL.
- **Gabimi gjatë dërgimit të raportit:** Nëse merrni error kur dërgoni një raport, kontrolloni nëse rruga `$reports_dir = '../../../Akreditimet/Reports/';` është e saktë në raport me vendndodhjen e skedarit `procesi_raportit.php`.
- **Karakteret e çuditshme në krye të faqes:** Nëse shihni `@ -1,670 +0,0 @@`, hapni `index.php` dhe fshini çdo tekst që ndodhet para tagut hapës `<?php`.
