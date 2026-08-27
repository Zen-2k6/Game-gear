USE gamegear_hub;

INSERT IGNORE INTO Category(categoryName) VALUES ('Gaming Chairs');

INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,status,imageURL)
SELECT categoryID,
       'Vortex Elite Gaming Chair',
       'Ergonomic gaming chair designed for long competitive sessions.',
       'High-back ergonomic design\nAdjustable lumbar support\n4D armrests\nReclines up to 155 degrees',
       460000,
       8,
       'Active',
       'https://www.topmarket.co.il/images/detailed/58/1Chair_RED_03.jpg'
FROM Category
WHERE categoryName='Gaming Chairs'
  AND NOT EXISTS (SELECT 1 FROM Product WHERE productName='Vortex Elite Gaming Chair');

INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,status,imageURL)
SELECT categoryID,'Noble Ice Gaming Chair','Premium white and black racing chair with full ergonomic adjustment.','Cold-foam seat\n4D armrests\nAdjustable neck and lumbar cushions\nClass-4 gas lift',590000,6,'Active','https://www.dateks.lv/images/pic/1200/1200/867/2212.jpg'
FROM Category WHERE categoryName='Gaming Chairs' AND NOT EXISTS (SELECT 1 FROM Product WHERE productName='Noble Ice Gaming Chair');

INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,status,imageURL)
SELECT categoryID,'Crimson Racer Gaming Chair','Sport-inspired gaming chair with supportive cushions and a sturdy base.','High-back racing design\nRemovable lumbar cushion\nHeight and tilt adjustment\nFive-wheel metal base',385000,10,'Active','https://img.advice.co.th/images_nas/pic_product4/A0103856/A0103856OK_BIG_2.jpg'
FROM Category WHERE categoryName='Gaming Chairs' AND NOT EXISTS (SELECT 1 FROM Product WHERE productName='Crimson Racer Gaming Chair');

INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,status,imageURL)
SELECT categoryID,'Quelman Pro Gaming Chair','Wide ergonomic gaming chair made for comfortable extended sessions.','Molded foam padding\nAdjustable armrests\nReclining backrest\nSupports up to 136 kg',525000,7,'Active','https://target.scene7.com/is/image/Target/GUEST_e59a5ead-57a4-4c49-8d74-e23d1ba037d6'
FROM Category WHERE categoryName='Gaming Chairs' AND NOT EXISTS (SELECT 1 FROM Product WHERE productName='Quelman Pro Gaming Chair');
