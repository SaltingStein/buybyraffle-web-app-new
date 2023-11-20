# API Documentation for BuyByRaffle Vouchers Plugin

## Overview
The BuyByRaffle Vouchers plugin offers a set of REST API endpoints to manage and redeem e-pin vouchers. These endpoints facilitate the interaction with the voucher system programmatically, providing functionalities like voucher redemption, details retrieval, and voucher email distribution.

## Base URL
All API endpoints are relative to your WordPress site's base URL. For example: `https://yourwordpresssite.com/wp-json`

## Authentication
Selected API endpoints require JWT authentication. Ensure to include a valid JWT token in the `Authorization` header as `Bearer <token>`.

## Endpoints

### 1. Redeem Voucher
- **Endpoint**: `/pgs/v1/redeem-voucher`
- **Method**: `POST`
- **Description**: Redeems a voucher using its pin.
- **Permissions**: Restricted to users with 'manage_options' capability.
- **Request Parameters**:
  - `voucher_pin` (string, required): The pin of the voucher to be redeemed.
- **Response**:
  - Success: HTTP 200 with success message.
  - Error: HTTP 404/400 with error message.

### 2. Get Voucher Details
- **Endpoint**: `/pgs/v1/voucher`
- **Method**: `GET`
- **Description**: Retrieves details of a specific voucher.
- **Permissions**: Open access.
- **Query Parameters**:
  - `voucher_pin` (string, required): The pin of the voucher to get details for.
- **Response**:
  - Success: HTTP 200 with voucher details (JSON format).
  - Error: HTTP 404 with 'Voucher not found' message.

### 3. Send Vouchers by Email
- **Endpoint**: `/buybyraffle/v1/sendvouchersbymail`
- **Method**: `POST`
- **Description**: Processes a batch ID to send voucher details via email.
- **Permissions**: JWT authentication.
- **Request Body**:
  - `batch_id` (string, required): The ID of the batch to process.
  - `user_email` (string, required): Email address to send voucher details to.
- **Response**:
  - Success: HTTP 200 with confirmation message.
  - Error: HTTP 400/404 with error message.

## Error Codes and Responses
- `400 Bad Request`: Invalid request format or missing parameters.
- `404 Not Found`: Requested resource or voucher not found.
- `401 Unauthorized`: Authentication failure or insufficient permissions.

## Notes
- Ensure all request data is properly formatted and URL-encoded where necessary.
- For secured endpoints, always include a valid JWT token in the request header.
