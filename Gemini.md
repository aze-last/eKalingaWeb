# Gemini CLI Project Rules

## developer's note: always install and use skill /ui-ux-pro-max and laravel-best-practices for better prompting and ui ux making  always use php artisan commands for changes, database, migration, seeder, etc. The database should be written is only the ams hostinger and the ams.db the ggms and crs databases are only write and when the dev asked to wipeout or delete all the data inside the databases keep in mind dont touch the ggms and crs. the ggms and crs should not be touched or wipedout, lastly, when asked to wipeout the database only touch the ams tables dont touch the ggms and crs related tables and the users table to letme login again.

You are my senior Laravel coding partner for this repo.

## Project Context

- **Framework:** Laravel 12/13 with PHP 8.3+
- **Frontend Stack:** Livewire 4.4, Alpine.js, Tailwind CSS v4 (CSS-first `@theme` in `resources/css/app.css`)
- **PDF Generation:** Barryvdh Laravel DomPDF
- **Database & Storage:** SQLite (`ams.db` / `database/database.sqlite`) & Eloquent ORM
- **Testing & Quality:** PHPUnit 12, Laravel Pint (`vendor/bin/pint --format agent`)

Base all help on the actual repository structure and current implementation. Follow Laravel conventions for routes, controllers, Livewire components, models, migrations, policies, requests, services, Blade views, and tests. Use Tailwind CSS v4 utilities.

## Active Project Modules

The application is organized into the following active modules:
1. **Dashboard** (`/dashboard` - `App\Livewire\Dashboard\Overview`)
2. **Masterlist & Citizen Profiles** (`/masterlist`, `/masterlist/{civilRegistryId}` - `App\Livewire\Masterlist\Index`, `App\Livewire\Masterlist\Profile`)
3. **Budget Workspace** (`/budget` - `App\Livewire\Budget\Workspace`)
4. **Project Distribution & Live Preview** (`/distribution`, `/distribution/live-preview/{project}` - `App\Livewire\Distribution\Workspace`, `App\Livewire\Distribution\LivePreview`)
5. **GGMS Transactions Ledger** (`/ggms` - `App\Livewire\Ggms\TransactionLedger`)
6. **Reports & PDF Export** (`/reports`, `/reports/download-pdf` - `App\Livewire\Reports\Builder`, `App\Http\Controllers\ReportPdfController`)

## Repo Safety & Database Constraints

There are 2 related repos:
- **Private repo:** `Ayuda-Maangement-System` (safe place to push everything)
- **Public repo:** `BarangayAyudaSys` (never expose or push secrets, tokens, credentials, or private config)

**DATABASE SAFETY:**
- Agents must **NEVER** execute hard delete queries or delete rows from the database.
- Deletions are strictly reserved for the developer.
- If a feature requires removing records, implement **Soft Delete** (`is_active = false` or `deleted_at`) or prompt the developer.

## User Management & Permissions

- **Roles:** Strictly `SuperAdmin` and `Admin` (`App\Enums\UserRole`).
- **SuperAdmin Exemption:** Only `SuperAdmin` can access System User Management. SuperAdmins are exempt from module permission checks.
- **Self-Protection:** Users (including SuperAdmins) cannot delete or deactivate their own currently logged-in account.
- **Admin Soft-Deletion:** SuperAdmins may soft-delete other accounts.
- **Protected Settings:** Accessing "App Database" or "GGMS Budget Source" settings requires password re-verification.

## eKalinga+ UI/UX Theme Lock (Tailwind CSS v4)

All modules must strictly adhere to this design system to ensure visual consistency:

### 1. Core Color Palette — "Barangay Heraldic"
Defined in `resources/css/app.css`:
```css
@theme {
    --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;

    --color-brand: #15803D;
    --color-accent: #F59E0B;
    --color-sidebar: #F8FAFC;
    --color-surface: #FFFFFF;
    --color-page-bg: #F1F5F9;
    --color-neutral-strong: #0F172A;
    --color-success: #15803D;
    --color-error: #BE123C;
    --color-warning: #854D0E;
}
```
- **Error/Red (`#BE123C`):** Exclusively for errors, destructive actions, and remove affordances. Never as chrome or decoration.
- **Amber/Gold (`#F59E0B`):** Fills-only with dark text on top. Never amber/yellow text on a light background.
- **Banned Colors:** No random teals or unapproved blue chrome.

### 2. Typography Standards
| Element | Classes |
| --- | --- |
| Module header | `text-2xl font-bold text-brand` |
| Sidebar section header | `text-[13px] font-bold uppercase tracking-wide text-brand` |
| Sidebar buttons | `text-sm font-medium text-foreground` |
| Table text | `text-xs` or `text-[13px]` for dense tables |
| Card label | `text-xs font-bold text-muted-foreground` |
| Card value | `text-2xl font-black` |

### 3. Structural Constraints
- **Sidebar:** Fixed `w-80` (320px), light background (`bg-sidebar`).
- **Main content padding:** `p-[30px]`.
- **Corner radius:** `rounded-xl` to `rounded-2xl` for cards/panels; `rounded-md` to `rounded-lg` for buttons.
- **Card styling:** `border border-slate-200` or soft `shadow-sm`/`shadow`. Never use `shadow-xl` or `shadow-2xl`.

### 4. Overlay & Modal Standard
Every Create/Edit/Add/Payout action opens as an overlay panel above the main view:
- **Backdrop:** `backdrop-blur-md` with `bg-[#0F172A]/80` scrim.
- **Behavior:** The underlying table/list remains mounted and visible.
- **Alpine.js Transitions:**
```html
x-transition:enter="ease-out duration-200"
x-transition:enter-start="opacity-0 scale-95"
x-transition:enter-end="opacity-100 scale-100"
x-transition:leave="ease-in duration-150"
x-transition:leave-start="opacity-100 scale-100"
x-transition:leave-end="opacity-0 scale-95"
```

### 5. Module Layout Structure
- **Left (Sidebar, `bg-sidebar`):** Navigation, active filters, search, and primary action buttons (amber/gold).
- **Center:** Operational data — tables, lists, cards, and live metrics.
- **Right (Optional):** Selected item details, activity feeds, or action summaries.

## Core Behavioral Guidelines

- **Minimal Edits:** Modify only relevant files. Do not refactor untouched code or change public APIs unless requested.
- **No Hallucinations:** Use only actual existing models, columns, routes, and services.
- **Code First:** Output paste-ready, production code first.
- **Verification:** Run `vendor/bin/pint --format agent` on modified PHP files and `php artisan test --compact` when applicable.

## Compact Mode (`/compact`)

When `/compact` rule is active:
1. Keep replies as short as possible.
2. Output the result first without filler, intros, or repeated request text.
3. State missing/risky items only if needed in one short line.
4. Prioritize token efficiency.

Response structure:
1. result
2. missing/risky items only if needed
3. stop

## Obsidian Daily Reporting Rules
At the end of an active coding session or when explicitly requested, document changes in the Obsidian wiki:
1. **Location:** `C:\Users\ASUS\OneDrive\Desktop\Projects-wiki\Daily Logs\<YYYY-MM-DD>.md`
2. **Content:**
   - Modified files list.
   - Purpose of each change.
   - Comprehensive summary of work done.