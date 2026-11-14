ALTER TABLE `orders`
  MODIFY `persons` varchar(50) NOT NULL DEFAULT '',
  MODIFY `OrderType` enum('Dine In','Take Away','Takeout','Delivery') NOT NULL,
  ADD COLUMN IF NOT EXISTS `PaymentMethod` enum('Cash','KHQR') DEFAULT 'Cash' AFTER `OrderType`,
  MODIFY `message` varchar(100) NOT NULL DEFAULT '',
  MODIFY `reject_message` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `tablebookings`
  MODIFY `CustomerID` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `WalkInLabel` varchar(100) DEFAULT NULL AFTER `NumberOfGuests`,
  MODIFY `BookingStatus` enum('Pending','Reserved','Confirmed','CheckedIn','CheckedOut','Cancelled','Completed') DEFAULT 'Pending';
