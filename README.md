
---

# DOKUMENTACIONI I PROJEKTIT: Agjencia e Kosovës për Akreditim (AKA)
**Ekipi:** 4 Zhvillues
**Lënda:** Programimi në ueb nga ana e Serverit
**Qëllimi:** Përmbushja e Fazës I (20 Prill) dhe parapërgatitja për Fazën II (25 Maj).

## 1. Logjika dhe Struktura e Përgjithshme (Arkitektura)
Ne po përdorim një qasje "Modular/Lite-MVC". Kjo do të thotë që nuk po shkruajmë gjithçka në një skedar `index.php`, por e kemi ndarë projektin në blloqe logjike në mënyrë që të punojmë paralelisht pa i prishur kodin njëri-tjetrit:

* **`/includes`**: Përmban pjesët e përsëritura të dizajnit (`header.php`, `footer.php`, navigimet).
* **`/classes`**: Këtu vendoset e gjithë logjika e objekt-orientuar (OOP). Këtu jetojnë klasat pa HTML (p.sh. `User.php`, `Institution.php`).
* **`/public`**: Faqet që mund t'i shohë kushdo (Ballina, Rreth Nesh, `login.php`).
* **`/dashboard`**: Zona e Kuqe (E Mbrojtur). Askush nuk hyn këtu pa kaluar përmes skedarit të sigurisë `authentication.php`. Brenda saj ndahet në `/admin` dhe `/user`.
* **`/assets`**: CSS, Imazhet dhe skriptat JS.

---

## 2. Ndarja e Roleve (Detyrat për secilin Anëtar)

Për të marrë pikët maksimale, çdo anëtar do të marrë "pronësinë" e një kërkese specifike nga rubrika e vlerësimit.

### 👤 Anëtari 1 (Eldi) - Lead Backend & Siguria
*Meqenëse ti e ke ngritur arkitekturën bazë, ti do të menaxhosh fluksin e të dhënave.*
* **Detyrat e Fazës 1:**
    * Menaxhimi përfundimtar i `login.php`, **Sessions** dhe **Cookies** (Kërkesa: 2 pikë).
    * Sigurimi i rrugëzimit të saktë me `authentication.php`.
* **Faza 2 (Përgatitja):** Do të merresh me Sigurinë (Mbrojtja nga SQL Injection, XSS, dhe Hashing i fjalëkalimeve).

### 👤 Anëtari 2 (Endriti) - Arkitekti i OOP dhe Validimeve RegEx
*Ky anëtar do të fokusohet te llogaritjet dhe logjika prapa skenës.*
* **Detyrat e Fazës 1:**
    * Zgjerimi i skedarëve në folderin `/classes` (**OOP - 1 pikë**). Të sigurohet që kemi metoda Get/Set të plota dhe minimum 2 klasa të domenit me trashëgimi.
    * Ndërtimi i logjikës së **Validimit me RegEx (1 pikë)**. Krijimi i një forme p.sh. "Shto Përdorues" ose "Regjistro Institucion" ku emaili dhe numri i telefonit validohen në server me RegEx para se të procedohen.
* **Faza 2 (Përgatitja):** Do të fokusohet te ndërtimi i skripteve **AJAX** për operacionet asinkrone.

### 👤 Anëtari 3 (Zgjim) - UI/UX & Menaxhimi i Vargjeve (Arrays)
*Ky anëtar sigurohet që projekti të duket profesional dhe navigimi të funksionojë saktë.*
* **Detyrat e Fazës 1:**
    * Implementimi i struktures **Include/Require (1 pikë)** për Header/Footer në të gjitha faqet.
    * Dizajnimi i tabelave dhe listave. Përdorimi i **Multidimensional Arrays (1 pikë)** dhe unazave (`foreach`) për të shfaqur të dhënat "dummy" kudo në projekt.
    * Përdorimi i operatorëve dhe funksioneve të stringjeve/datave brenda UI-t.
* **Faza 2 (Përgatitja):** Krijimi i formave HTML që do të përdoren për operacionet e plota CRUD (Create, Read, Update, Delete).

### 👤 Anëtari 4 (Drenisi) - DevOps, Dokumentimi & Databaza
*Ky anëtar mban përgjegjësi për dorëzimin dhe integrimin e kodit.*
* **Detyrat e Fazës 1:**
    * Krijimi dhe menaxhimi i repozitorit në **GitHub**. Zgjidhja e konflikteve (Merge conflicts) kur anëtarët dërgojnë kodin.
    * Shkrimi i skedarit **README.md (1 pikë nga dorëzimi)** me udhëzime të sakta se si asistentët ta ekzekutojnë projektin (p.sh., si ta ndezin XAMPP, cilat llogari testuese të përdorin).
    * Inçizimi i **Video Demonstrimit** (kërkesë e dorëzimit).
* **Faza 2 (Përgatitja):** Do të jetë "Database Administrator". Dizajnon skemën relacionale në MySQL (Minimum 3 tabela) dhe përgatit skedarin `.sql` për ta lidhur me projektin.

---

## 3. Rregullat e Artë për Ekipin (GitHub Workflow)
Për të mos fshirë punën e njëri-tjetrit:
1.  **Askush nuk punon direkt në degën (branch) `main`!**
2.  Secili anëtar krijon degën e tij për detyrën që ka. P.sh., Anëtari 2 hap terminalin dhe shkruan: `git checkout -b validimi-regex`.
3.  Pasi e përfundon detyrën, e bën "Push" në GitHub dhe hap një **Pull Request**.
4.  Anëtari 4 e rishikon kodin dhe e bën "Merge" me degën kryesore.

---
