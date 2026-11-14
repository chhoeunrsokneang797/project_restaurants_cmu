-- Run this once on restaurantpos to enable customer profile photos.
ALTER TABLE Customers
    ADD COLUMN ProfilePhoto VARCHAR(255) NULL AFTER PasswordHash;
