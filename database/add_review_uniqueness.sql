USE gamegear_hub;

-- Run this once on an existing installation. New installations already include it.
-- Keep the newest review if older data contains more than one review per customer/product.
DELETE older
FROM Review older
JOIN Review newer
  ON newer.customerID = older.customerID
 AND newer.productID = older.productID
 AND newer.reviewID > older.reviewID;

ALTER TABLE Review
  ADD CONSTRAINT uq_review_customer_product UNIQUE (customerID, productID);
