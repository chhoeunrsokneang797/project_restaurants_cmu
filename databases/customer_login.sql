-- Run this once on the restaurantpos database to enable customer login.
ALTER TABLE Customers
    ADD COLUMN PasswordHash VARCHAR(255) NULL AFTER Email;

-- Customers can create accounts on customer_login.php. To set a password for
-- an existing customer account, generate a hash in PHP with:
-- php -r "echo password_hash('choose-a-password', PASSWORD_DEFAULT), PHP_EOL;"
--
-- Set the password for an existing customer using the generated hash:
-- UPDATE Customers
-- SET PasswordHash = '<paste-generated-hash>'
-- WHERE Phone = '<customer-phone>';
--
-- Staff may also create an account directly with a generated hash:
-- INSERT INTO Customers (CustomerName, Phone, Email, PasswordHash, CustomerType, Role)
-- VALUES ('Customer name', '0123456789', 'customer@example.com',
--         '<paste-generated-hash>', 'Regular', 'Customer');
