USE gamegear_hub;

-- Safe catalog add-on for an existing GameGear database.
-- Each product is inserted only when a product with the same name is not present.

INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 1,'Razer BlackWidow V4 75%','A hot-swappable compact mechanical keyboard with immersive underglow.','75% aluminum layout\nHot-swappable switches\nDoubleshot ABS keycaps\nPer-key RGB',385000,9,'https://assets3.razerzone.com/SRsaiRU8_8_QnhoHF2nq5Smizt8=/1920x1280/https%3A%2F%2Fmedias-p1.phoenix.razer.com%2Fsys-master-phoenix-images-container%2Fhf7%2Fh5a%2F9640099119134%2Fblackwidow-v4-75-black-2-500x500.png'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Razer BlackWidow V4 75%');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 1,'Logitech G PRO X TKL Lightspeed','Tournament-focused wireless keyboard with pro-grade response.','Tenkeyless layout\nLIGHTSPEED wireless\nGX mechanical switches\nRGB lighting',520000,6,'https://cdn.cs.1worldsync.com/20/17/2017dd16-e8f1-447a-a996-f9e12cfbf2f0.jpg'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Logitech G PRO X TKL Lightspeed');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 1,'Keychron Q1 Max','Premium wireless custom keyboard with a full-metal gasket-mounted build.','75% CNC aluminum body\n2.4GHz and Bluetooth\nHot-swappable PCB\nDouble-gasket design',650000,5,'https://www.keychron.com/cdn/shop/files/Keychron-Q1-Max-QMK-VIA-Wireless-Custom-Mechanical-Keyboard-75_-Layout-Aluminum-White-Fully-Assembled-Knob-for-Mac-Windows-Linux-Gateron-Jupiter-Red.jpg?v=1753685590&width=1200'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Keychron Q1 Max');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 2,'Logitech G PRO X Superlight 2','Ultra-light wireless esports mouse with a next-generation optical sensor.','HERO 2 sensor\n32000 DPI\n60g weight\nUSB-C charging',445000,12,'https://resource.logitechg.com/w_1200%2Ch_900%2Car_4%3A3%2Cc_pad%2Cq_auto%2Cf_auto/d_transparent.gif/content/dam/gaming/en/products/pro-x-superlight-2/new-gallery-assets-2025/pro-x-superlight-2-mice-top-angle-white-gallery-1.png'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Logitech G PRO X Superlight 2');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 2,'Razer Viper V3 Pro','Symmetrical wireless mouse tuned with professional esports players.','Focus Pro 35K sensor\n54g weight\n8000Hz polling\n95-hour battery',470000,10,'https://asset.productmarketingcloud.com/api/assetstorage/1995_a50426df-6945-4d1c-a23b-5ab1ddb31e5b/3088392.jpg'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Razer Viper V3 Pro');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 3,'SteelSeries Arctis Nova 7 Wireless','Multi-platform wireless headset for gaming, calls, and music.','2.4GHz plus Bluetooth\n360° spatial audio\n38-hour battery\nRetractable microphone',495000,8,'https://images.ctfassets.net/hmm5mo4qf4mf/3p2rnBYIX4crdjTafcDJGQ/07b9db56b7acca8513a3250d5e52c1ab/arctis_nova_pdp_img_buy_1.png__1920x1080_crop-fit_optimize_subsampling-2-177.png?fit=scale&fm=webp&q=90&w=1200'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='SteelSeries Arctis Nova 7 Wireless');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 3,'HyperX Cloud III Wireless','Long-lasting wireless comfort with clear positional game audio.','120-hour battery\n53mm angled drivers\nDetachable microphone\nMemory foam cushions',430000,11,'https://hp.widen.net/content/ila3sgmjzt/png/ila3sgmjzt.png?color=ffffff00&dpi=72&h=900&w=1200'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='HyperX Cloud III Wireless');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 4,'Xbox Wireless Controller 2025','Refined wireless controller for Xbox, PC, mobile, and cloud gaming.','Bluetooth and Xbox Wireless\nHybrid D-pad\nTextured triggers\nShare button',285000,14,'https://cms-assets.xboxservices.com/assets/62/96/62965c6a-429c-47b1-b901-ff69a6081d43.jpg?n=Xbox-Wireless-Controller_Image-Hero-768_Black_1920x831_03.jpg'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Xbox Wireless Controller 2025');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 4,'DualSense Edge Wireless Controller','Customizable high-performance controller with replaceable stick modules.','Adjustable trigger stops\nRemappable back buttons\nChangeable stick caps\nUSB-C connection',720000,4,'https://gmedia.playstation.com/is/image/SIEPDC/DualSense-Edge-image-block-05-en-03oct22?$1600px$'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='DualSense Edge Wireless Controller');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 5,'Corsair iCUE LINK QX120 RGB Kit','Smart daisy-chain cooling fans with vivid dual-zone lighting.','3 × 120mm PWM fans\n34 RGB LEDs per fan\nMagnetic dome bearing\niCUE LINK hub included',390000,7,'https://www.scan.co.uk/images/infopages/corsair_fans/QX120/starterkit/topimgb.png'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Corsair iCUE LINK QX120 RGB Kit');
INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL)
SELECT 5,'Lian Li UNI FAN TL LCD 120 Kit','Premium modular fans with customizable edge displays.','3 × 120mm fans\n1.6-inch LCD displays\nDaisy-chain design\nL-Connect 3 control',510000,6,'https://www.proshop.de/Images/1600x1200/3224052_f22e6dd29ceb.jpg'
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE productName='Lian Li UNI FAN TL LCD 120 Kit');
