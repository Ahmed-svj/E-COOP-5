# E-COOP

Cooperative Training Management System for Jubail Industrial College (Department of Business Administration).
Students, companies, academic supervisors and the COOP office manage placements, daily attendance codes,
final reports, evaluations and institutional reports in one bilingual (English / Arabic) web app.

## Run it

Open `index.html` in a browser. No build step, server or internet connection is needed.

## Publish with GitHub Pages

1. Create a new repository on GitHub and upload everything in this folder (keep the folder structure).
2. Go to **Settings → Pages**.
3. Under **Build and deployment**, choose **Deploy from a branch**, branch `main`, folder `/ (root)`, and save.
4. After a minute the site is live at `https://<your-username>.github.io/<repository-name>/`.

## Demo accounts

On the sign-in page choose **Use a demo account instead**, or sign in with any password:

| Role | Email |
|---|---|
| Student | ahmed.almousa@jic.edu.sa |
| Student | yazeed.alqahtani@jic.edu.sa |
| Company | coop@easternlogistics.sa |
| Supervisor | abdullah.alrumi@jic.edu.sa |
| COOP Office (Admin) | coop.office@jic.edu.sa |

## Project structure

```
E-COOP/
├── index.html                    Entry page: loads the styles and scripts below, in order
├── README.md
├── .nojekyll                     Tells GitHub Pages to serve the files as they are
├── assets/images/
│   └── e-coop-logo.webp          Logo and browser-tab icon
├── css/
│   ├── tokens.css                Design tokens: colours, type, spacing (light + dark)
│   ├── components.css            Design-system component styles
│   ├── app.css                   Signed-in screens: header, tiles, cards, chat, reports, print layout
│   └── landing.css               Public pages: home, sign in, create account, contact
└── js/
    ├── vendor/                   React 18.3.1 (react, react-dom)
    ├── components/
    │   └── coop-components.js    Button, StatusBadge, Alert, TextField, DataTable, StatCard...
    ├── config.js                 Settings (logo path)
    ├── i18n/
    │   ├── strings.js            English + Arabic text
    │   └── strings-extra.js      More text, including company profile and reports
    ├── data/
    │   ├── saudi-cities.js       Saudi cities by region
    │   └── seed-data.js          Sample users, companies, placements, attendance, chats
    ├── file-store.js             Saves uploaded report files in the browser (IndexedDB)
    ├── landing.js                Home page, contact form, create account
    └── app.js                    Student, company, supervisor and COOP office screens
```

## Notes

- Script order in `index.html` matters: libraries, components, settings/text/data, then pages.
- All data is saved in each visitor's own browser (localStorage / IndexedDB). A shared database would need a server.
- Sample records are fictional.
