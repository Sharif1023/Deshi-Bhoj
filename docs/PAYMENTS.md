# bKash / Nagad payment setup

This application uses **SSLCOMMERZ hosted checkout** for both wallets. It is not a direct bKash Tokenized Checkout or direct Nagad merchant SDK integration. Cash payment works without any gateway account.

## Configuration

Obtain your SSLCOMMERZ merchant store ID/password, and have bKash and Nagad enabled for that store. The exact channel identifiers depend on the gateway account; confirm Nagad's identifier in the merchant configuration/API response. The configurable default is `nagad`; it has not been verified against your account.

```dotenv
APP_URL=https://your-domain.example
PAYMENTS_ENABLED=true
SSLCOMMERZ_SANDBOX=true
SSLCOMMERZ_STORE_ID=your_sandbox_store_id
SSLCOMMERZ_STORE_PASSWORD=your_sandbox_store_password
SSLCOMMERZ_BKASH_CHANNEL=bkash
SSLCOMMERZ_NAGAD_CHANNEL=nagad
```

Gateway callbacks require a publicly reachable URL. Localhost cannot receive provider IPN. Configure the merchant panel IPN endpoint as `https://your-domain.example/payment/ipn`. Success/fail/cancel URLs are sent by the application during session initiation.

## Payment lifecycle

1. Customer chooses bKash/Nagad and submits checkout. Server snapshots current products/prices, saves the order as unpaid, and returns a private order page.
2. Customer presses Pay. The server creates a unique transaction and requests a hosted session with the chosen wallet filter.
3. Customer completes the provider's hosted checkout. PIN/OTP are never collected by this application.
4. IPN or success callback supplies a validation ID. The application calls the provider validation API using secret store credentials.
5. Transaction ID, BDT currency, exact amount, bank transaction reference, status and risk level must match. Only verified low-risk results become paid. Risky transactions or payments arriving after cancellation become review.
6. Repeated valid callbacks cannot duplicate orders or regress a paid order. A manager cannot mark an unpaid online order paid or fulfill it.

Untrusted fail/cancel redirects only display information. They never modify the ledger. Pending gateway sessions are reused. A unique constraint permits one payment attempt per order. An ambiguous initiation timeout is held for reconciliation to avoid accidental double charging. Staff must inspect the gateway portal before cancelling/replacing such an order. Automatic payment retries after expired sessions, automated refunds, and automated dispute settlement are not implemented. Refunds are processed in the merchant portal and must be reconciled with restaurant records.

## Required acceptance tests with your account

- Verify that selecting each wallet actually opens the correct enabled channel.
- Success, insufficient funds, cancellation, delayed IPN, lost browser session and timeout.
- Duplicate IPN, wrong amount/currency, unknown transaction, risk level 1.
- Customer pays after restaurant cancels: held for review, no automatic fulfillment.
- Reconcile merchant portal reference and collected revenue.
- Switch to production credentials and `SSLCOMMERZ_SANDBOX=false` only after approval. Enable HTTPS and `SESSION_SECURE=true`.

No provider credentials were supplied for this conversion. Real gateway requests, wallet routing and settlements have **not** been tested. The included payment tests exercise validation logic with explicit fixtures; they do not simulate success in the running application.

Official integration reference: https://developer.sslcommerz.com/doc/v4/index.html
Merchant onboarding: https://sslcommerz.com/
