

CREATE TABLE `AQI_CHART_DETAILS` (
  `CHART_STANDARD` varchar(200) NOT NULL,
  `CLASSIFICATION_LABEL` varchar(200) NOT NULL,
  `MIN_VALUE` varchar(200) NOT NULL,
  `MAX_VALUE` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO AQI_CHART_DETAILS VALUES
("NPCB","GOOD","0","50"),
("NPCB","SATISFACTORY","51","100");




CREATE TABLE `AQI_CHART_PARAMETER_SCALINGS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CHART_STANDARD` varchar(200) NOT NULL,
  `CLASSIFICATION_LABEL` varchar(200) NOT NULL,
  `AQI_PARAMETER` varchar(200) NOT NULL,
  `MIN_VAL` varchar(200) NOT NULL,
  `MAX_VAL` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;


INSERT INTO AQI_CHART_PARAMETER_SCALINGS VALUES
("1","NPCB","GOOD","PM10","0","50"),
("2","NPCB","SATISFACTORY","PM10","51","100");




CREATE TABLE `Aqi_values_per_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(200) NOT NULL,
  `locationId` bigint(20) DEFAULT NULL,
  `branchId` bigint(20) DEFAULT NULL,
  `facilityId` int(100) DEFAULT NULL,
  `buildingId` bigint(20) DEFAULT NULL,
  `floorId` bigint(20) DEFAULT NULL,
  `labId` bigint(20) DEFAULT NULL,
  `deviceId` bigint(20) DEFAULT NULL,
  `AqiValue` varchar(200) DEFAULT NULL,
  `sampled_date_time` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17500 DEFAULT CHARSET=latin1;


INSERT INTO Aqi_values_per_device VALUES
("160","PL10245","42","45","","39","26","30","65","758","2022-09-12 13:57:59"),
("161","A-TEST","4","3","","2","2","3","70","0","");




CREATE TABLE `Aqi_values_per_deviceSensor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(200) NOT NULL,
  `locationId` bigint(20) DEFAULT NULL,
  `branchId` bigint(20) DEFAULT NULL,
  `facilityId` int(100) DEFAULT NULL,
  `buildingId` bigint(20) DEFAULT NULL,
  `floorId` bigint(20) DEFAULT NULL,
  `labId` bigint(20) DEFAULT NULL,
  `deviceId` bigint(20) DEFAULT NULL,
  `sensorId` int(10) DEFAULT NULL,
  `AqiValue` varchar(200) DEFAULT NULL,
  `sampled_date_time` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7938 DEFAULT CHARSET=latin1;


INSERT INTO Aqi_values_per_deviceSensor VALUES
("1","A-TEST","4","3","4","2","2","3","112","245","338.1","2022-12-07 15:42:59"),
("2","A-TEST","4","3","4","2","2","3","112","244","0","2022-12-07 15:42:59");




CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `type` varchar(225) DEFAULT NULL,
  `login_fail_attempt` int(10) NOT NULL DEFAULT '0',
  `blocked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO admin VALUES
("1","admin","admin@rdl","Admin","4","0"),
("1","admin","admin@rdl","Admin","4","0");




CREATE TABLE `aidealab_companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyName` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodicBackupInterval` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataRetentionPeriodInterval` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO aidealab_companies VALUES
("1","Ai-DEA Labs Pvt. Ltd","jananes@aidealabs.com","90","95","2022-10-13 11:53:58","2022-10-13 11:53:58"),
("2","A-TEST","puneethraj138@gmail.com","25","30","2022-12-27 14:48:03","2022-12-27 14:48:03");




CREATE TABLE `alert` (
  `id` int(11) NOT NULL,
  `machine_name` varchar(200) NOT NULL,
  `a_date` date NOT NULL,
  `a_time` time NOT NULL,
  `message` text NOT NULL,
  `r_date` date NOT NULL,
  `r_time` time NOT NULL,
  `remarks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO alert VALUES
("1","Punching Machine","2021-05-12","11:00:00","Oil Change","2021-05-18","10:46:12","rrrr"),
("2","Punching Machine","2021-05-12","11:00:00","Oil Change","2021-05-18","10:57:44","ttyyyuu");




CREATE TABLE `alert_crons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_date` date DEFAULT NULL,
  `a_time` time DEFAULT NULL,
  `companyCode` varchar(200) DEFAULT NULL,
  `deviceId` text,
  `sensorId` varchar(200) DEFAULT NULL,
  `sensorTag` varchar(200) DEFAULT NULL,
  `alertType` varchar(200) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  `msg` varchar(200) DEFAULT NULL,
  `severity` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `statusMessage` varchar(225) DEFAULT NULL,
  `Reason` varchar(225) DEFAULT NULL,
  `alarmType` varchar(225) DEFAULT NULL,
  `alertStandardMessage` text,
  `alertTriggeredDuration` text,
  `alertCategory` varchar(225) DEFAULT NULL,
  `triggeredAlertFlag` varchar(225) DEFAULT NULL,
  `timeDurations` varchar(225) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1309 DEFAULT CHARSET=latin1;


INSERT INTO alert_crons VALUES
("1","2022-09-29","09:21:01","A-TEST","3","52","NH3_gas1","outOfRange","1535.5","OUT OF RANGE VALUE IS HIGH","HIGH","0","NotCleared","","Latch","","","","","","2022-09-29 03:51:01"),
("2","2022-09-29","09:21:01","A-TEST","3","51","SO2_gas1","outOfRange","-48.5","OUT OF RANGE VALUE IS LOW","NORMAL","1","Cleared","Values are Normal","UnLatch","","","","","","2022-09-29 03:51:01");




CREATE TABLE `alert_cronsOLD2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_date` date DEFAULT NULL,
  `a_time` time DEFAULT NULL,
  `companyCode` varchar(200) DEFAULT NULL,
  `deviceId` text,
  `sensorId` varchar(200) DEFAULT NULL,
  `sensorTag` varchar(200) DEFAULT NULL,
  `alertType` varchar(200) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  `msg` varchar(200) DEFAULT NULL,
  `severity` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `statusMessage` varchar(225) DEFAULT NULL,
  `Reason` varchar(225) DEFAULT NULL,
  `alarmType` varchar(225) DEFAULT NULL,
  `alertCategory` varchar(225) DEFAULT NULL,
  `triggeredAlertFlag` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;


INSERT INTO alert_cronsOLD2 VALUES
("59","2022-10-27","14:17:01","A-TEST","3","52","NH3_gas1","outOfRange","1535.5","OUT OF RANGE VALUE IS HIGH","HIGH","0","NotCleared","","Latch","1","1","2022-10-27 08:47:01"),
("60","2022-10-27","14:17:01","A-TEST","3","51","SO2_gas1","outOfRange","-48.5","OUT OF RANGE VALUE IS LOW","LOW","0","NotCleared","","UnLatch","1","1","2022-10-27 08:47:01");




CREATE TABLE `alert_cronsOld` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_date` date DEFAULT NULL,
  `a_time` time DEFAULT NULL,
  `companyCode` varchar(200) DEFAULT NULL,
  `deviceId` text,
  `sensorId` varchar(200) DEFAULT NULL,
  `sensorTag` varchar(200) DEFAULT NULL,
  `alertType` varchar(200) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  `msg` varchar(200) DEFAULT NULL,
  `severity` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `statusMessage` varchar(225) DEFAULT NULL,
  `Reason` varchar(225) DEFAULT NULL,
  `alarmType` varchar(225) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `alert_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(225) DEFAULT NULL,
  `machine_name` varchar(100) DEFAULT NULL,
  `alert` text,
  `a_time` time DEFAULT NULL,
  `a_date` date DEFAULT NULL,
  `flag` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=890 DEFAULT CHARSET=latin1;


INSERT INTO alert_data VALUES
("46","CusVer","shajithahmed@aidealabs.com","Due date exceeded by one day for bumptest for O2","15:58:50","2022-11-18","1"),
("47","CusVer","abhishek@rdltech.in","Due date exceeded by one day for bumptest for O2","15:58:50","2022-11-18","1");




CREATE TABLE `alert_energy_data` (
  `id` int(11) NOT NULL,
  `a_date` date NOT NULL,
  `a_time` time NOT NULL,
  `msg` text NOT NULL,
  `sensor` varchar(200) NOT NULL,
  `machine_name` varchar(200) NOT NULL,
  `location` varchar(200) NOT NULL,
  `status` varchar(200) NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `amc` (
  `id` int(20) NOT NULL,
  `aid` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `compeney` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `datefrom` date NOT NULL,
  `dateto` date NOT NULL,
  `service` varchar(50) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `asset_type` varchar(50) NOT NULL,
  `machine` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO amc VALUES
("40","1234","Ranjith Hegade L","abc@gmail.com","manjeshwar kerala india","abc","7019300364","2021-01-05","2021-02-06","5","Research","manager","It Asset","Laptop"),
("41","12345","Ranjith Hegade","abc@gmail.com","vorkady","abc","7019300364","2020-07-04","2021-08-29","5","Research","Management","It Asset","Printer");




CREATE TABLE `amc_cer` (
  `id` int(20) NOT NULL,
  `aid` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `compeney` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `datefrom` date NOT NULL,
  `dateto` date NOT NULL,
  `inspection` varchar(20) NOT NULL,
  `department_name` varchar(20) NOT NULL,
  `section_name` varchar(20) NOT NULL,
  `asset_type` varchar(20) NOT NULL,
  `machine` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO amc_cer VALUES
("5","12345","Ranjith Hegade L","abc@gmail.com","Alabe house","abc","7019300364","2020-12-09","2021-02-07","5","Research","Management","It Asset","Printer"),
("6","1234","Ranjith Hegade","abc@gmail.com","gfhj","dffdesfds","1234567890","2021-01-05","2024-06-05","4","Research","Management","It Asset","Laptop");




CREATE TABLE `application_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `versionNumber` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO application_versions VALUES
("9","1.0","Initial Version","2022-11-18 11:31:59","2022-12-24 10:25:07"),
("14","1.2","Second Version","2022-12-24 11:00:15","2022-12-24 11:01:13");




CREATE TABLE `aqi_chart_config_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `aqiTemplateName` text COLLATE utf8mb4_unicode_ci,
  `aqiGoodMinScale` text COLLATE utf8mb4_unicode_ci,
  `aqiGoodMaxScale` text COLLATE utf8mb4_unicode_ci,
  `aqiSatisfactoryMinScale` text COLLATE utf8mb4_unicode_ci,
  `aqiSatisfactoryMaxScale` text COLLATE utf8mb4_unicode_ci,
  `aqiModerateMinScale` text COLLATE utf8mb4_unicode_ci,
  `aqiModerateMaxScale` text COLLATE utf8mb4_unicode_ci,
  `aqiPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `aqiPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `aqiVeryPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `aqiVeryPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `aqiSevereMinScale` text COLLATE utf8mb4_unicode_ci,
  `aqiSevereMaxScale` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO aqi_chart_config_values VALUES
("1","chart1","0","50","51","100","101","200","201","300","301","400","401","500","","");




CREATE TABLE `aqmi_json_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime NOT NULL,
  `j_data` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=408684 DEFAULT CHARSET=latin1;


INSERT INTO aqmi_json_data VALUES
("407684","2022-12-29 18:11:39","{\"DATE\":\"2022-12-29\",\"TIME\":\"18:11:39\",\"COMPANY\":\"Cust12\",\"LOCATION\":\"52\",\"BRANCH\":\"56\",\"FACILITY\":\"45\",\"BULDING\":\"55\",\"FLOOR\":\"48\",\"LAB\":\"51\",\"DEVICE_ID\":\"123\",\"SD_CARD\":\"1\",\"RSSI\":\"28\",\"MODE\":\"2\",\"ACCESS_CODE\":\"1003\",\"Ozone_scr\":\"0.7\",\"PM10_out\":\"0.2\",\"AQMs_CO\":\"2.5\"}","2022-12-29 12:41:39",""),
("407685","2022-12-29 18:11:41","{\"DATE\":\"2022-12-29\",\"TIME\":\"18:11:41\",\"COMPANY\":\"Cust12\",\"LOCATION\":\"52\",\"BRANCH\":\"56\",\"FACILITY\":\"45\",\"BULDING\":\"55\",\"FLOOR\":\"48\",\"LAB\":\"51\",\"DEVICE_ID\":\"123\",\"SD_CARD\":\"1\",\"RSSI\":\"28\",\"MODE\":\"2\",\"ACCESS_CODE\":\"1003\",\"Ozone_scr\":\"0.7\",\"PM10_out\":\"0.2\",\"AQMs_CO\":\"2.5\"}","2022-12-29 12:41:41","");




CREATE TABLE `aqmi_json_dataRDL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime NOT NULL,
  `j_data` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6007 DEFAULT CHARSET=latin1;


INSERT INTO aqmi_json_dataRDL VALUES
("5007","2022-11-28 10:14:58","{\"DATE\":\"2022-11-28\",\"TIME\":\"10:6:26\",\"DEVICE_ID\":\"126\",\"MODE\":\"2\",\"ACCESS_CODE\":\"1003\",\"pm2.5_gas13\":\"-0.00\",\"NH3_gas1\":\"-0.00\",\"NO2_gas2\":\"-0.00\",\"SO2_gas1\":\"-0.00\"}","2022-11-28 04:44:58",""),
("5008","2022-11-28 10:15:08","{\"DATE\":\"2022-11-28\",\"TIME\":\"10:6:36\",\"DEVICE_ID\":\"126\",\"MODE\":\"2\",\"ACCESS_CODE\":\"1003\",\"pm2.5_gas13\":\"-0.00\",\"NH3_gas1\":\"-0.00\",\"NO2_gas2\":\"-0.00\",\"SO2_gas1\":\"-0.00\"}","2022-11-28 04:45:08","");




CREATE TABLE `aqmi_valuse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer` varchar(200) NOT NULL,
  `location` varchar(200) NOT NULL,
  `branch` varchar(200) NOT NULL,
  `facility` varchar(200) NOT NULL,
  `bld` varchar(11) NOT NULL,
  `flr` varchar(200) NOT NULL,
  `lab` varchar(200) NOT NULL,
  `par` varchar(200) NOT NULL,
  `val_n` varchar(200) NOT NULL,
  `key_n` varchar(200) NOT NULL,
  `active_in` varchar(200) NOT NULL,
  `deviceid` varchar(200) DEFAULT NULL,
  `dev_mode` varchar(50) NOT NULL,
  `access_code` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=latin1;


INSERT INTO aqmi_valuse VALUES
("19","PL10245","42","45","33","39","26","30","Oxygen","2.7","Oxygen_Indoor","1","64","2","1003"),
("20","PL10245","42","45","33","39","26","30","CO","","CO_Indoor","0","64","2","1003");




CREATE TABLE `branches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branchName` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordinates` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branches_ibfk_1` (`location_id`),
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO branches VALUES
("2","PR-TEST","1","Dakshina kannada","12.913798680796805,74.85669396972655","2022-03-31 22:20:43","2022-03-31 22:20:43"),
("3","A-TEST","4","Kochi","10.143532239590003,76.34190820312499","2022-03-31 22:46:40","2022-03-31 22:46:40");




CREATE TABLE `buildings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `facility_id` bigint(20) unsigned NOT NULL,
  `buildingName` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordinates` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `buildingTotalFloors` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `buildingDescription` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `buildingImg` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `buildingTag` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `buildings_ibfk_2` (`location_id`),
  KEY `buildings_ibfk_3` (`branch_id`),
  KEY `buildings_ibfk_4` (`facility_id`),
  CONSTRAINT `buildings_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `buildings_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `buildings_ibfk_4` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO buildings VALUES
("1","PR-TEST","1","2","1","Mech Building","12.866040059423135,74.92472954249372","6","Mech Lab Building","Customers/PR-TEST/Buildings/Mech Lab_Building.png","mech12345","2022-03-31 22:29:22","2022-03-31 22:29:53"),
("2","A-TEST","4","3","4","Fort Building","9.958011138238854,76.25536225279936","5","fort building with 5 floors","Customers/A-TEST/Buildings/Fort Building_Building.png","fort_kochi_tag","2022-03-31 22:55:19","2022-09-27 17:41:45");




CREATE TABLE `bumpTest_aqmi_json_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime NOT NULL,
  `j_data` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7948 DEFAULT CHARSET=latin1;


INSERT INTO bumpTest_aqmi_json_data VALUES
("729","2022-12-14 17:06:10","{\"DATE\":\"2022-12-14\",\"TIME\":\"17:6:33\",\"DEVICE_ID\":\"114\",\"MODE\":\"6\",\"ACCESS_CODE\":\"1003\",\"AQMi_CO2\":\"-0.00\",\"AQMi_Propane\":\"-0.00\",\"AQMi_Oxygen\":\"-0.00\",\"AQMi_CO\":\"-0.00\"}","",""),
("730","2022-12-14 17:06:12","{\"DATE\":\"2022-12-14\",\"TIME\":\"17:6:34\",\"DEVICE_ID\":\"114\",\"MODE\":\"6\",\"ACCESS_CODE\":\"1003\",\"AQMi_CO2\":\"-0.00\",\"AQMi_Propane\":\"-0.00\",\"AQMi_Oxygen\":\"-0.00\",\"AQMi_CO\":\"-0.00\"}","","");




CREATE TABLE `bump_test_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `device_id` bigint(20) unsigned DEFAULT NULL,
  `sensorTagName` text COLLATE utf8mb4_unicode_ci,
  `lastDueDate` text COLLATE utf8mb4_unicode_ci,
  `typeCheck` text COLLATE utf8mb4_unicode_ci,
  `percentageConcentrationGas` text COLLATE utf8mb4_unicode_ci,
  `durationPeriod` text COLLATE utf8mb4_unicode_ci,
  `displayedValue` text COLLATE utf8mb4_unicode_ci,
  `percentageDeviation` text COLLATE utf8mb4_unicode_ci,
  `calibrationDate` text COLLATE utf8mb4_unicode_ci,
  `nextDueDate` text COLLATE utf8mb4_unicode_ci,
  `result` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`),
  CONSTRAINT `bump_test_results_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO bump_test_results VALUES
("6","A-TEST","3","pm2.5_gas1","22-08-2022","span","23","5","35","2","2022-10-11 11:29:28","2022-10-18","Pass","2022-10-11 11:29:28","2022-10-11 11:29:28"),
("7","Cust12","120","AQMi_O2","NA","zeroCheck","0","120","0.75757575757576","0","2022-10-14 14:15:06","2022-10-17","Pass","2022-10-14 14:15:06","2022-10-14 14:15:06");




CREATE TABLE `calibration_test_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `sensorTag` text COLLATE utf8mb4_unicode_ci,
  `name` text COLLATE utf8mb4_unicode_ci,
  `model` text COLLATE utf8mb4_unicode_ci,
  `testResult` text COLLATE utf8mb4_unicode_ci,
  `calibrationDate` text COLLATE utf8mb4_unicode_ci,
  `nextDueDate` text COLLATE utf8mb4_unicode_ci,
  `calibratedDate` text COLLATE utf8mb4_unicode_ci,
  `lastDueDate` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO calibration_test_results VALUES
("13","A-TEST","O3_gas1","O3","model001","Fail","2022-05-10 14:49:28","26/07/2022","","","2022-05-10 21:49:28","2022-05-10 21:49:28"),
("14","PL10245","pm10","PM10","123","PASS","2022-06-21 18:19:37","31/07/2022","","","2022-06-22 01:19:37","2022-06-22 01:19:37");




CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `categoryName` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoryDescription` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO categories VALUES
("1","A-TEST","AQMI","Indoor category","2022-03-31 23:51:32","2022-07-23 23:50:36"),
("2","A-TEST","AQMO","Air Quality Manipulating","2022-04-01 23:21:08","2022-04-01 23:21:08");




CREATE TABLE `config_pars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parameter` varchar(200) NOT NULL,
  `value_n` varchar(200) NOT NULL,
  `deviceid` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;


INSERT INTO config_pars VALUES
("44","upload_interval","5","3"),
("45","upload_interval","15","102");




CREATE TABLE `config_setups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `accessType` text COLLATE utf8mb4_unicode_ci,
  `accessPointName` text COLLATE utf8mb4_unicode_ci,
  `ssId` text COLLATE utf8mb4_unicode_ci,
  `accessPointPassword` text COLLATE utf8mb4_unicode_ci,
  `accessPointNameSecondary` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ssIdSecondary` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessPointPasswordSecondary` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ftpAccountName` text COLLATE utf8mb4_unicode_ci,
  `userName` text COLLATE utf8mb4_unicode_ci,
  `ftpPassword` text COLLATE utf8mb4_unicode_ci,
  `port` text COLLATE utf8mb4_unicode_ci,
  `serverUrl` text COLLATE utf8mb4_unicode_ci,
  `folderPath` text COLLATE utf8mb4_unicode_ci,
  `serviceProvider` text COLLATE utf8mb4_unicode_ci,
  `apn` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO config_setups VALUES
("1","PR-TEST","","JIO12","JHJ011","qwer","","","","FTP001","Ranjith","ftp011","10.01.10","http:www.Server.com/","server/bng/server","JIO Relanice","09.0988","2022-04-01 16:59:50","2022-04-01 16:59:50"),
("2","A-TEST","","ABC","123","123456","abc2","12345","123456","testing1","Test01","123456","10","www.airtel.com","folder/folderName","Airtel","5G","2022-04-04 18:13:38","2022-08-06 00:26:04");




CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customerName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phoneNo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customerId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customerLogo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customerTheme` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `alertLogInterval` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deviceLogInterval` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sensorLogInterval` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodicBackupInterval` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataRetentionPeriodInterval` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expireDateReminder` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO customers VALUES
("1","A-Tech Solutions","abhishekshenoy7@gmail.com","8660839819","Mangalore","A-TEST","Customers/A-TEST/logo/customerLogo.png","","2022-03-31 21:20:53","2022-10-11 14:51:00","10","10","5","","","3"),
("2","Prajwal Testing","prajwaldk011@gmail.com","9449432758","Sahyadri","PR-TEST","Customers/PR-TEST/logo/customerLogo.png","","2022-03-31 21:22:56","2022-09-12 17:16:31","10","10","5","20","25","3");




CREATE TABLE `deviceDebug` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `deviceId` varchar(225) DEFAULT NULL,
  `deviceMode` varchar(225) DEFAULT NULL,
  `accessCode` varchar(225) DEFAULT NULL,
  `sdCard` varchar(10) DEFAULT NULL,
  `RSSI` varchar(10) DEFAULT NULL,
  `RTC` varchar(225) DEFAULT NULL,
  `time_stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `current_date_time` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=557 DEFAULT CHARSET=latin1;


INSERT INTO deviceDebug VALUES
("366","3","5","1003","1","28","2022-10-15 12:28:54","2022-10-15 06:59:52","2022-10-15 12:29:52"),
("367","3","5","1003","1","28","2022-10-15 12:30:04","2022-10-15 07:01:01","2022-10-15 12:31:01");




CREATE TABLE `device_config_setups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` bigint(20) unsigned NOT NULL,
  `deviceName` text COLLATE utf8mb4_unicode_ci,
  `accessType` text COLLATE utf8mb4_unicode_ci,
  `accessPointName` text COLLATE utf8mb4_unicode_ci,
  `ssId` text COLLATE utf8mb4_unicode_ci,
  `accessPointPassword` text COLLATE utf8mb4_unicode_ci,
  `accessPointNameSecondary` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ssIdSecondary` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessPointPasswordSecondary` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ftpAccountName` text COLLATE utf8mb4_unicode_ci,
  `userName` text COLLATE utf8mb4_unicode_ci,
  `ftpPassword` text COLLATE utf8mb4_unicode_ci,
  `port` text COLLATE utf8mb4_unicode_ci,
  `serverUrl` text COLLATE utf8mb4_unicode_ci,
  `folderPath` text COLLATE utf8mb4_unicode_ci,
  `serviceProvider` text COLLATE utf8mb4_unicode_ci,
  `apn` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`),
  CONSTRAINT `device_config_setups_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO device_config_setups VALUES
("1","PR-TEST","1","AQMI010","","JIO12","JHJ011","qwer","","","","FTP001","Ranjith","ftp011","10.01.10","http:www.Server.com/","server/bng/server","JIO Relanice","09.0988","2022-04-01 17:01:07","2022-04-01 17:01:07"),
("2","A-TEST","3","Indoor Device-01","","ABC","linksys","2020@RDL","testing123","testing123","testing123","testing1","Test01","123456","10","www.airtel.com","folder/folderName","Airtel","5G","2022-04-04 20:16:30","2022-08-06 00:37:10");




CREATE TABLE `device_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `facility_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `floor_id` bigint(20) unsigned NOT NULL,
  `lab_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `categoryName` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` bigint(20) unsigned NOT NULL,
  `deviceName` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `assetTag` text COLLATE utf8mb4_unicode_ci,
  `macAddress` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `deviceIcon` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `floorCords` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_locations_location_id_foreign` (`location_id`),
  KEY `device_locations_branch_id_foreign` (`branch_id`),
  KEY `device_locations_facility_id_foreign` (`facility_id`),
  KEY `device_locations_building_id_foreign` (`building_id`),
  KEY `device_locations_floor_id_foreign` (`floor_id`),
  KEY `device_locations_lab_id_foreign` (`lab_id`),
  KEY `device_locations_category_id_foreign` (`category_id`),
  KEY `device_locations_device_id_foreign` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `device_model_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyName` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deviceId` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deviceName` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deviceModel` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currentDateTime` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO device_model_logs VALUES
("1","A-TEST","143","Test-AQMO-2","","1.0","","2022-12-17 16:18:28","2022-12-17 16:18:28"),
("2","A-TEST","143","Test-AQMO-2","","1.1","","2022-12-17 16:32:25","2022-12-17 16:32:25");




CREATE TABLE `devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `facility_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `floor_id` bigint(20) unsigned DEFAULT NULL,
  `floorCords` text COLLATE utf8mb4_unicode_ci,
  `lab_id` bigint(20) unsigned DEFAULT NULL,
  `deviceName` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `deviceCategory` text COLLATE utf8mb4_unicode_ci,
  `firmwareVersion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `macAddress` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `deviceTag` text COLLATE utf8mb4_unicode_ci,
  `aqiIndex` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nonPollingPriority` text COLLATE utf8mb4_unicode_ci,
  `pollingPriority` text COLLATE utf8mb4_unicode_ci,
  `dataPushUrl` text COLLATE utf8mb4_unicode_ci,
  `firmwarePushUrl` text COLLATE utf8mb4_unicode_ci,
  `binFileName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deviceImage` text COLLATE utf8mb4_unicode_ci,
  `deviceMode` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT 'enabled',
  `firmwareStatus` tinyint(1) NOT NULL DEFAULT '0',
  `configurationStatus` tinyint(1) NOT NULL DEFAULT '0',
  `xAxisTimeInterval` text COLLATE utf8mb4_unicode_ci,
  `disconnectedStatus` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT '1',
  `lastAnalogMemoryAddressIndex` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `lastDigitalMemoryAddressIndex` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `lastModbusMemoryAddressIndex` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `configurationProcessStatus` varchar(225) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hardwareModelVersion` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `devices_location_id_foreign` (`location_id`),
  KEY `devices_branch_id_foreign` (`branch_id`),
  KEY `devices_facility_id_foreign` (`facility_id`),
  KEY `devices_building_id_foreign` (`building_id`),
  KEY `devices_category_id_foreign` (`category_id`),
  KEY `floor_id` (`floor_id`),
  KEY `lab_id` (`lab_id`),
  CONSTRAINT `devices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `devices_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `devices_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `devices_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`),
  CONSTRAINT `devices_ibfk_2` FOREIGN KEY (`lab_id`) REFERENCES `lab_departments` (`id`),
  CONSTRAINT `devices_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO devices VALUES
("1","PR-TEST","1","2","1","1","1","","1","AQMI010","1","AQMI","qw123","01.01.01.90","AQ918919","","20:59","08:58","http://varmatrix.com/Aqms/blog/public/Customers/PR-TEST/Buildings/devices/ConfigSettingFile/AQMI010_DataPush.json","http://varmatrix.com/Aqms/blog/public/Customers/PR-TEST/Buildings/devices/ConfigSettingFile","","","enabled","0","0","","1","0","0","0","2","","2022-03-31 23:57:03","2022-03-31 23:57:03"),
("2","PR-TEST","1","2","1","1","1","","1","AQMO0725","1","AQMI","787878","24:D7:EB:87:19:1D","AQo001","","03:59","22:43","http://varmatrix.com/Aqms/blog/public/Customers/PR-TEST/Buildings/devices/ConfigSettingFile/AQMO0725_DataPush.json","http://varmatrix.com/Aqms/blog/public/Customers/PR-TEST/Buildings/devices/ConfigSettingFile","","Customers/PR-TEST/Buildings/devices/AQMO0725.png","enabled","0","1","","1","0","0","0","1","","2022-04-01 00:00:49","2022-09-19 16:23:01");




CREATE TABLE `email_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calibrartionSubject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calibrartionBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bumpTestSubject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bumpTestBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stelSubject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stelBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twaSubject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twaBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warningSubject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warningBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criticalSubject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criticalBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outOfRangeSubject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outOfRangeBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodicitySubject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodicityBody` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO email_templates VALUES
("14","A-TESTs","Calibration Duedate testing ","Calibration testing body","Bumptest Duedate testing","Bumptest due date testing  for ","StelAlarm","stel message","twaAlarm","twa message","warningAlarm","warning message","criticalAlarm","critical Message","outofRangeAlarmSUBject","outodrange MessageBODYto be displayed","periodicityAlarm","periodicity Message","2022-10-10 17:46:04","2022-10-10 17:46:50");




CREATE TABLE `emp_users` (
  `id` bigint(20) unsigned NOT NULL,
  `empId` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobileno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emprole` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `companycode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `facilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `facilityName` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordinates` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `facilities_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `facilities_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO facilities VALUES
("1","PR-TEST","1","2","Sahyadri","12.865501396094254,74.92329858398436","2022-03-31 22:22:13","2022-04-01 22:54:22"),
("3","PR-TEST","1","2","Urva Market","12.890059083618072,74.83008645629882","2022-03-31 22:24:00","2022-04-01 22:54:48");




CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `firmware_version_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deviceName` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firmwareVersion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hardwareVersion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO firmware_version_reports VALUES
("1","","AQI1 SENSORq","","v.12","","2022-08-22 15:58:35","2022-08-22 15:58:35"),
("2","","AQI1 SENSORq","","01","","2022-08-22 16:01:22","2022-08-22 16:01:22");




CREATE TABLE `floors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `facility_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `floorStage` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `floorName` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `floorMap` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `floorCords` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `floors_location_id_foreign` (`location_id`),
  KEY `floors_branch_id_foreign` (`branch_id`),
  KEY `floors_facility_id_foreign` (`facility_id`),
  KEY `floors_building_id_foreign` (`building_id`),
  CONSTRAINT `floors_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `floors_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `floors_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `floors_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO floors VALUES
("1","PR-TEST","1","2","1","1","5","MechRdl floor","Customers/PR-TEST/Buildings/Floors/5.png","48.25125369679825,7.0151705478790225,false","2022-03-31 22:34:34","2022-04-01 23:41:24"),
("2","A-TEST","4","3","4","2","2","Management Floor","Customers/A-TEST/Buildings/Floors/2.png","65.83882942821329,15.167196991611224,false","2022-03-31 23:10:46","2022-09-27 17:44:44");




CREATE TABLE `gas_cylinders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gasCylinderName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiryDate` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO gas_cylinders VALUES
("1","A-TEST","NO2 Cylinder-1","2022-09-04","2022-09-22 07:22:19","2022-09-22 14:53:53"),
("3","A-TEST","SO2 Cylinder","2022-09-30","2022-09-22 14:53:28","2022-09-26 09:31:05");




CREATE TABLE `lab_departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `facility_id` bigint(20) unsigned DEFAULT NULL,
  `building_id` bigint(20) unsigned DEFAULT NULL,
  `floor_id` bigint(20) unsigned DEFAULT NULL,
  `labDepName` text COLLATE utf8mb4_unicode_ci,
  `labDepMap` text COLLATE utf8mb4_unicode_ci,
  `labCords` text COLLATE utf8mb4_unicode_ci,
  `labHooterStatus` int(225) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lab_departments_location_id_foreign` (`location_id`),
  KEY `lab_departments_branch_id_foreign` (`branch_id`),
  KEY `lab_departments_facility_id_foreign` (`facility_id`),
  KEY `lab_departments_building_id_foreign` (`building_id`),
  KEY `lab_departments_floor_id_foreign` (`floor_id`),
  CONSTRAINT `lab_departments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_departments_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_departments_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_departments_floor_id_foreign` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_departments_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO lab_departments VALUES
("1","PR-TEST","1","2","1","1","1","MHG Lab","Customers/PR-TEST/Buildings/Floors/Department/MHG Lab.png","[{\"left\":16.63473092786853,\"top\":54.1502830229443},{\"left\":29.729466402520217,\"top\":41.70591539558398},{\"left\":24.74099574551005,\"top\":33.133128807846866},{\"left\":44.9027313175928,\"top\":17.646804649354017},{\"left\":33.88652528336202,\"top\":4.0962710106727735},{\"left\":11.646260270858368,\"top\":22.901093203128376},{\"left\":13.93264265532136,\"top\":30.6442552823748},{\"left\":11.438407326816277,\"top\":43.91824741822581}]","1","2022-03-31 23:00:20","2022-09-16 11:55:38"),
("3","A-TEST","4","3","4","2","2","Chem Lab","Customers/A-TEST/Buildings/Floors/Department/Chem Lab.png","[{\"left\":59.94971103686065,\"top\":26.228964228500697},{\"left\":59.2449816419369,\"top\":10.928735095208625},{\"left\":82.8534163718824,\"top\":11.3261436441253},{\"left\":82.8534163718824,\"top\":28.61341552200076}]","1","2022-04-01 17:24:59","2022-12-22 16:04:02");




CREATE TABLE `locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stateName` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordinates` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO locations VALUES
("1","PR-TEST","Karnataka","","2022-03-31 22:06:53","2022-03-31 22:06:53"),
("2","PR-TEST","Tripura","","2022-03-31 22:17:34","2022-03-31 22:17:34");




CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO migrations VALUES
("1","2014_10_12_000000_create_users_table","1"),
("2","2014_10_12_100000_create_password_resets_table","1");




CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO personal_access_tokens VALUES
("557","App\\Models\\User","37","janu.sathy@hotmail.com","35d6eaa853de74b63eccadf47f928e71dfaa1eceb7aa0152afd08acee757b9d8","[\"*\"]","2022-04-26 21:36:59","2022-04-26 21:36:16","2022-04-26 21:36:59"),
("609","App\\Models\\User","33","mageshwarig@aidealabs.com","93ac0b7f68d2feadb13400e83f7b98b7d3828ff7c0b6b0492c39eaa33c7fb266","[\"*\"]","2022-04-27 22:09:18","2022-04-27 22:07:44","2022-04-27 22:09:18");




CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `productName` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO products VALUES
("1","Samsung A30","samnsung mobile with high features","23","20","","");




CREATE TABLE `relay_output_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `a_date` text COLLATE utf8mb4_unicode_ci,
  `a_time` text COLLATE utf8mb4_unicode_ci,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `deviceId` text COLLATE utf8mb4_unicode_ci,
  `sensorId` text COLLATE utf8mb4_unicode_ci,
  `sensorTag` text COLLATE utf8mb4_unicode_ci,
  `alertType` text COLLATE utf8mb4_unicode_ci,
  `severity` text COLLATE utf8mb4_unicode_ci,
  `statusMessage` text COLLATE utf8mb4_unicode_ci,
  `relayOutputStatus` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `relay_output_resultsTest` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `a_date` text COLLATE utf8mb4_unicode_ci,
  `a_time` text COLLATE utf8mb4_unicode_ci,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `deviceId` text COLLATE utf8mb4_unicode_ci,
  `sensorId` text COLLATE utf8mb4_unicode_ci,
  `sensorTag` text COLLATE utf8mb4_unicode_ci,
  `alertType` text COLLATE utf8mb4_unicode_ci,
  `severity` text COLLATE utf8mb4_unicode_ci,
  `statusMessage` text COLLATE utf8mb4_unicode_ci,
  `relayOutputStatus` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=866 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO relay_output_resultsTest VALUES
("1","2022-11-10","10:39:01","A-TEST","3","46","pm2.5_gas1","Critical","LOW","DISABLED","0","",""),
("2","2022-11-10","10:39:01","","","","","outOfRange","HIGH","DISABLED","0","","");




CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customerId` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rolename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rolecode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO roles VALUES
("1","aqms","edirt","2003","2022-07-01 21:16:00","2022-07-19 21:16:12"),
("2","aqmsdgvfadsvgf","edirtssss","2009","2022-07-07 21:16:06","2022-07-25 21:16:16");




CREATE TABLE `sampled_sensor_data_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(10) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `parameterName` varchar(200) NOT NULL,
  `last_val` varchar(200) NOT NULL,
  `max_val` varchar(200) NOT NULL,
  `min_val` varchar(200) NOT NULL,
  `avg_val` varchar(200) NOT NULL,
  `sample_date_time` varchar(200) NOT NULL,
  `alertType` text,
  `sevierity` text,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `param_unit` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `sampled_sensor_data_details_MinMaxAvg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(10) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `parameterName` varchar(200) NOT NULL,
  `last_val` varchar(200) NOT NULL,
  `max_val` varchar(200) NOT NULL,
  `min_val` varchar(200) NOT NULL,
  `avg_val` float NOT NULL,
  `sample_date_time` varchar(200) NOT NULL,
  `current_date_time` varchar(225) DEFAULT NULL,
  `alertType` text,
  `alertStandardMessage` text,
  `sevierity` text,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `param_unit` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=261441 DEFAULT CHARSET=latin1;


INSERT INTO sampled_sensor_data_details_MinMaxAvg VALUES
("129","3","50","O3","50.5","50.5","50.5","51","2022-09-06 17:18:01","","NORMAL","","NORMAL","2022-09-06 11:48:07","ppm"),
("130","64","134","CO","279.6","279.6","279.6","280","2022-09-06 17:18:01","","NORMAL","","NORMAL","2022-09-06 11:48:07","ppm");




CREATE TABLE `segregatedBumptestValues` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(225) DEFAULT NULL,
  `sensorTag` varchar(225) DEFAULT NULL,
  `deviceMode` varchar(225) DEFAULT NULL,
  `accessCode` varchar(225) DEFAULT NULL,
  `val` varchar(225) DEFAULT NULL,
  `scaledVal` varchar(225) DEFAULT NULL,
  `current_date_time` varchar(225) DEFAULT NULL,
  `upload_date_time` varchar(225) DEFAULT NULL,
  `timeStamps` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `segregatedValues` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(225) DEFAULT NULL,
  `sensorTag` varchar(225) DEFAULT NULL,
  `deviceMode` varchar(225) DEFAULT NULL,
  `accessCode` varchar(225) DEFAULT NULL,
  `val` varchar(225) DEFAULT NULL,
  `scaledVal` float DEFAULT NULL,
  `current_date_time` varchar(225) DEFAULT NULL,
  `upload_date_time` varchar(225) DEFAULT NULL,
  `timeStamps` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1453616 DEFAULT CHARSET=latin1;


INSERT INTO segregatedValues VALUES
("1452616","125","SD_CARD","2","1003","1","1","2022-12-30 14:48:01","2022-12-30 14:47:38","2022-12-30 09:18:02"),
("1452617","125","RSSI","2","1003","28","28","2022-12-30 14:48:01","2022-12-30 14:47:38","2022-12-30 09:18:02");




CREATE TABLE `segregatedValuesRDL` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(225) DEFAULT NULL,
  `sensorTag` varchar(225) DEFAULT NULL,
  `deviceMode` varchar(225) DEFAULT NULL,
  `accessCode` varchar(225) DEFAULT NULL,
  `val` varchar(225) DEFAULT NULL,
  `scaledVal` varchar(22) DEFAULT NULL,
  `current_date_time` varchar(225) DEFAULT NULL,
  `upload_date_time` varchar(225) DEFAULT NULL,
  `timeStamps` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `sensor_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `sensorName` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sensorDescriptions` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `measureUnitList` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO sensor_categories VALUES
("2","A-TEST","Gases","Gas Sensor Category","\"[]\"","2022-04-01 19:41:25","2022-04-02 02:01:42"),
("3","A-TEST","Light","Light sensor","[{\"unitLabel\":\"ppm\",\"unitMeasure\":\"ppm desc\"}]","2022-04-01 23:27:50","2022-07-28 21:27:22");




CREATE TABLE `sensor_limit_change_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sensor_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criticalMinValue` text COLLATE utf8mb4_unicode_ci,
  `criticalMaxValue` text COLLATE utf8mb4_unicode_ci,
  `warningMinValue` text COLLATE utf8mb4_unicode_ci,
  `warningMaxValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeMinValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeMaxValue` text COLLATE utf8mb4_unicode_ci,
  `email` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO sensor_limit_change_logs VALUES
("5","A-TEST","3","14","OLD - 25 AND NEW - 25","OLD - 75 AND NEW - 75","OLD - 40 AND NEW - 40","OLD - 60 AND NEW - 60","OLD - 10 AND NEW - 10","OLD - 90 AND NEW - 90","developer2@rdltech.in","2022-07-11 16:39:26","2022-07-11 16:39:26"),
("6","A-TEST","3","14","OLD - 25 AND NEW - 25","OLD - 75 AND NEW - 75","OLD - 40 AND NEW - 40","OLD - 60 AND NEW - 60","OLD - 10 AND NEW - 10","OLD - 90 AND NEW - 90","prajwaldk011@gmail.com","2022-07-11 16:53:42","2022-07-11 16:53:42");




CREATE TABLE `sensor_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` text COLLATE utf8mb4_unicode_ci,
  `sensorCategoryId` bigint(20) unsigned NOT NULL,
  `sensorCategoryName` text COLLATE utf8mb4_unicode_ci,
  `sensorName` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sensorOutput` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sensorType` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `units` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minRatedReading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minRatedReadingChecked` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `minRatedReadingScale` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `maxRatedReading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maxRatedReadingChecked` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `maxRatedReadingScale` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slaveId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registerId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `length` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registerType` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conversionType` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ipAddress` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subnetMask` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criticalMinValue` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criticalMaxValue` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warningMinValue` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warningMaxValue` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outofrangeMinValue` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outofrangeMaxValue` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isStel` text COLLATE utf8mb4_unicode_ci,
  `stelDuration` text COLLATE utf8mb4_unicode_ci,
  `stelStartTime` time DEFAULT NULL,
  `stelType` text COLLATE utf8mb4_unicode_ci,
  `stelLimit` text COLLATE utf8mb4_unicode_ci,
  `stelAlert` text COLLATE utf8mb4_unicode_ci,
  `twaDuration` text COLLATE utf8mb4_unicode_ci,
  `twaStartTime` time DEFAULT NULL,
  `twaType` text COLLATE utf8mb4_unicode_ci,
  `twaLimit` text COLLATE utf8mb4_unicode_ci,
  `twaAlert` text COLLATE utf8mb4_unicode_ci,
  `alarm` text COLLATE utf8mb4_unicode_ci,
  `unLatchDuration` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `isAQI` text COLLATE utf8mb4_unicode_ci,
  `parmGoodMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmGoodMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmSatisfactoryMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmSatisfactoryMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmModerateMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmModerateMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmVeryPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmVeryPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmSevereMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmSevereMaxScale` text COLLATE utf8mb4_unicode_ci,
  `relayOutput` text COLLATE utf8mb4_unicode_ci,
  `bumpTestRequired` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sensor_id` (`sensorCategoryId`),
  CONSTRAINT `sensor_units_ibfk_1` FOREIGN KEY (`sensorCategoryId`) REFERENCES `sensor_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO sensor_units VALUES
("3","","2","","O2 Sensor","AB Co.","95130003","Modbus","TCP","Centigrade","4","1","1","20","1","100","6","13","32 Bit","3","Double","192.168.5.50","192.255.255.255","","","","","","","0","","","","","0","","","","","","","","2022-04-01 19:42:59","2022-04-04 19:43:52","","","","","","","","","","","","","","ON","OFF"),
("4","","3","","Light Rigister","XYZ Co.","rdl1001","Analog","0-10v","20","23","1","1","34","1","100","","","","","","","","","","","","","","0","","","","","0","","","","","","","","2022-04-01 23:31:02","2022-04-04 19:46:55","","","","","","","","","","","","","","ON","ON");




CREATE TABLE `sensors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `facility_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `floor_id` bigint(20) unsigned DEFAULT NULL,
  `lab_id` bigint(20) unsigned DEFAULT NULL,
  `categoryId` bigint(20) unsigned DEFAULT NULL,
  `deviceCategory` text COLLATE utf8mb4_unicode_ci,
  `sensorCategoryId` bigint(20) unsigned DEFAULT NULL,
  `sensorCategoryName` text COLLATE utf8mb4_unicode_ci,
  `deviceId` bigint(20) unsigned DEFAULT NULL,
  `deviceName` text COLLATE utf8mb4_unicode_ci,
  `sensorName` bigint(20) unsigned DEFAULT NULL,
  `sensorNameUnit` text COLLATE utf8mb4_unicode_ci,
  `conversionType` text COLLATE utf8mb4_unicode_ci,
  `sensorOutput` text COLLATE utf8mb4_unicode_ci,
  `sensorType` text COLLATE utf8mb4_unicode_ci,
  `sensorTag` text COLLATE utf8mb4_unicode_ci,
  `registerId` text COLLATE utf8mb4_unicode_ci,
  `registerType` text COLLATE utf8mb4_unicode_ci,
  `slaveId` text COLLATE utf8mb4_unicode_ci,
  `subnetMask` text COLLATE utf8mb4_unicode_ci,
  `units` text COLLATE utf8mb4_unicode_ci,
  `ipAddress` text COLLATE utf8mb4_unicode_ci,
  `length` text COLLATE utf8mb4_unicode_ci,
  `maxRatedReading` text COLLATE utf8mb4_unicode_ci,
  `maxRatedReadingChecked` text COLLATE utf8mb4_unicode_ci,
  `maxRatedReadingScale` text COLLATE utf8mb4_unicode_ci,
  `minRatedReading` text COLLATE utf8mb4_unicode_ci,
  `minRatedReadingChecked` text COLLATE utf8mb4_unicode_ci,
  `minRatedReadingScale` text COLLATE utf8mb4_unicode_ci,
  `pollingIntervalType` text COLLATE utf8mb4_unicode_ci,
  `criticalMinValue` text COLLATE utf8mb4_unicode_ci,
  `criticalMaxValue` text COLLATE utf8mb4_unicode_ci,
  `criticalAlertType` text COLLATE utf8mb4_unicode_ci,
  `criticalLowAlert` text COLLATE utf8mb4_unicode_ci,
  `criticalHighAlert` text COLLATE utf8mb4_unicode_ci,
  `warningMinValue` text COLLATE utf8mb4_unicode_ci,
  `warningMaxValue` text COLLATE utf8mb4_unicode_ci,
  `warningAlertType` text COLLATE utf8mb4_unicode_ci,
  `warningLowAlert` text COLLATE utf8mb4_unicode_ci,
  `warningHighAlert` text COLLATE utf8mb4_unicode_ci,
  `outofrangeMinValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeMaxValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeAlertType` text COLLATE utf8mb4_unicode_ci,
  `outofrangeLowAlert` text COLLATE utf8mb4_unicode_ci,
  `outofrangeHighAlert` text COLLATE utf8mb4_unicode_ci,
  `digitalAlertType` text COLLATE utf8mb4_unicode_ci,
  `digitalLowAlert` text COLLATE utf8mb4_unicode_ci,
  `digitalHighAlert` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `isAQI` text COLLATE utf8mb4_unicode_ci,
  `parmGoodMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmGoodMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmSatisfactoryMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmSatisfactoryMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmModerateMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmModerateMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmVeryPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmVeryPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmSevereMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmSevereMaxScale` text COLLATE utf8mb4_unicode_ci,
  `isStel` text COLLATE utf8mb4_unicode_ci,
  `stelDuration` text COLLATE utf8mb4_unicode_ci,
  `stelStartTime` time DEFAULT NULL,
  `stelType` text COLLATE utf8mb4_unicode_ci,
  `stelLimit` text COLLATE utf8mb4_unicode_ci,
  `stelAlert` text COLLATE utf8mb4_unicode_ci,
  `twaDuration` text COLLATE utf8mb4_unicode_ci,
  `twaStartTime` time DEFAULT NULL,
  `twaType` text COLLATE utf8mb4_unicode_ci,
  `twaLimit` text COLLATE utf8mb4_unicode_ci,
  `twaAlert` text COLLATE utf8mb4_unicode_ci,
  `alarm` text COLLATE utf8mb4_unicode_ci,
  `unLatchDuration` text COLLATE utf8mb4_unicode_ci,
  `relayOutput` text COLLATE utf8mb4_unicode_ci,
  `sensorFault` text COLLATE utf8mb4_unicode_ci,
  `sensorStatus` tinyint(1) NOT NULL DEFAULT '1',
  `notificationStatus` tinyint(1) NOT NULL DEFAULT '1',
  `hooterRelayStatus` tinyint(1) NOT NULL DEFAULT '1',
  `audioDecibelLevel` text COLLATE utf8mb4_unicode_ci,
  `relayMessage` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deviceReadStatus` tinyint(1) NOT NULL DEFAULT '0',
  `memoryAddressValue` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criticalRefMinValue` text COLLATE utf8mb4_unicode_ci,
  `criticalRefMaxValue` text COLLATE utf8mb4_unicode_ci,
  `warningRefMinValue` text COLLATE utf8mb4_unicode_ci,
  `warningRefMaxValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeRefMinValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeRefMaxValue` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `branch_id` (`branch_id`),
  KEY `facility_id` (`facility_id`),
  KEY `building_id` (`building_id`),
  KEY `sensors_ibfk_7` (`floor_id`),
  KEY `sensors_ibfk_8` (`lab_id`),
  KEY `categoryId` (`categoryId`),
  KEY `deviceId` (`deviceId`),
  KEY `sensorName` (`sensorName`),
  KEY `sensorCategoryId` (`sensorCategoryId`)
) ENGINE=InnoDB AUTO_INCREMENT=317 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO sensors VALUES
("14","A-TEST","4","3","4","2","2","3","1","AQMI","3","Light","3","Indoor Device-01","11","NO2","","Digital","","Test Sen","","3","1","","","","","55","1","100","45","0","1","Priority","25","75","Both","critical value is low","critical value is high","40","60","Both","warning value is low","warning value is high","10","90","Both","OUT OF RANGE VALUE IS LOW","OUT OF RANGE VALUE IS HIGH","Low","low alert","","2022-04-08 23:09:41","2022-10-27 15:38:54","","","","","","","","","","","","","","0","","00:00:00","ppm","500000","","","13:30:00","ppm","500000","","Latch","","","","1","1","0","98","DISABLED","1","650","25","75","40","60","10","90"),
("22","A-TEST","4","3","4","2","2","3","1","AQMI","2","Gases","3","Indoor Device-01","3","O2 Sensor","Double","Modbus","TCP","modbus tester","13","3","1","192.255.255.255","Centigrade","192.168.5.50","32 Bit","55","1","100","45","0","1","Priority","25","75","Both","critical value is low","critical value is high","40","60","Both","warning value is low","warning value is high","10","85","Both","OUT OF RANGE VALUE IS LOW","OUT OF RANGE VALUE IS HIGH","","","","2022-04-14 23:45:02","2022-10-27 17:39:53","","","","","","","","","","","","","","0","","00:00:00","ppm","500000","","","13:30:00","ppm","500000","","Latch","","ON","","1","1","0","97","DISABLED","1","1010","25","75","40","60","10","90");




CREATE TABLE `sensorsOLD` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `facility_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `floor_id` bigint(20) unsigned DEFAULT NULL,
  `lab_id` bigint(20) unsigned DEFAULT NULL,
  `categoryId` bigint(20) unsigned DEFAULT NULL,
  `deviceCategory` text COLLATE utf8mb4_unicode_ci,
  `sensorCategoryId` bigint(20) unsigned DEFAULT NULL,
  `sensorCategoryName` text COLLATE utf8mb4_unicode_ci,
  `deviceId` bigint(20) unsigned DEFAULT NULL,
  `deviceName` text COLLATE utf8mb4_unicode_ci,
  `sensorName` bigint(20) unsigned DEFAULT NULL,
  `sensorNameUnit` text COLLATE utf8mb4_unicode_ci,
  `conversionType` text COLLATE utf8mb4_unicode_ci,
  `sensorOutput` text COLLATE utf8mb4_unicode_ci,
  `sensorType` text COLLATE utf8mb4_unicode_ci,
  `sensorTag` text COLLATE utf8mb4_unicode_ci,
  `registerId` text COLLATE utf8mb4_unicode_ci,
  `registerType` text COLLATE utf8mb4_unicode_ci,
  `slaveId` text COLLATE utf8mb4_unicode_ci,
  `subnetMask` text COLLATE utf8mb4_unicode_ci,
  `units` text COLLATE utf8mb4_unicode_ci,
  `ipAddress` text COLLATE utf8mb4_unicode_ci,
  `length` text COLLATE utf8mb4_unicode_ci,
  `maxRatedReading` text COLLATE utf8mb4_unicode_ci,
  `maxRatedReadingChecked` text COLLATE utf8mb4_unicode_ci,
  `maxRatedReadingScale` text COLLATE utf8mb4_unicode_ci,
  `minRatedReading` text COLLATE utf8mb4_unicode_ci,
  `minRatedReadingChecked` text COLLATE utf8mb4_unicode_ci,
  `minRatedReadingScale` text COLLATE utf8mb4_unicode_ci,
  `pollingIntervalType` text COLLATE utf8mb4_unicode_ci,
  `criticalMinValue` text COLLATE utf8mb4_unicode_ci,
  `criticalMaxValue` text COLLATE utf8mb4_unicode_ci,
  `criticalAlertType` text COLLATE utf8mb4_unicode_ci,
  `criticalLowAlert` text COLLATE utf8mb4_unicode_ci,
  `criticalHighAlert` text COLLATE utf8mb4_unicode_ci,
  `warningMinValue` text COLLATE utf8mb4_unicode_ci,
  `warningMaxValue` text COLLATE utf8mb4_unicode_ci,
  `warningAlertType` text COLLATE utf8mb4_unicode_ci,
  `warningLowAlert` text COLLATE utf8mb4_unicode_ci,
  `warningHighAlert` text COLLATE utf8mb4_unicode_ci,
  `outofrangeMinValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeMaxValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeAlertType` text COLLATE utf8mb4_unicode_ci,
  `outofrangeLowAlert` text COLLATE utf8mb4_unicode_ci,
  `outofrangeHighAlert` text COLLATE utf8mb4_unicode_ci,
  `digitalAlertType` text COLLATE utf8mb4_unicode_ci,
  `digitalLowAlert` text COLLATE utf8mb4_unicode_ci,
  `digitalHighAlert` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `isAQI` text COLLATE utf8mb4_unicode_ci,
  `parmGoodMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmGoodMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmSatisfactoryMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmSatisfactoryMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmModerateMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmModerateMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmVeryPoorMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmVeryPoorMaxScale` text COLLATE utf8mb4_unicode_ci,
  `parmSevereMinScale` text COLLATE utf8mb4_unicode_ci,
  `parmSevereMaxScale` text COLLATE utf8mb4_unicode_ci,
  `isStel` text COLLATE utf8mb4_unicode_ci,
  `stelDuration` text COLLATE utf8mb4_unicode_ci,
  `stelStartTime` time DEFAULT NULL,
  `stelType` text COLLATE utf8mb4_unicode_ci,
  `stelLimit` text COLLATE utf8mb4_unicode_ci,
  `stelAlert` text COLLATE utf8mb4_unicode_ci,
  `twaDuration` text COLLATE utf8mb4_unicode_ci,
  `twaStartTime` time DEFAULT NULL,
  `twaType` text COLLATE utf8mb4_unicode_ci,
  `twaLimit` text COLLATE utf8mb4_unicode_ci,
  `twaAlert` text COLLATE utf8mb4_unicode_ci,
  `alarm` text COLLATE utf8mb4_unicode_ci,
  `unLatchDuration` text COLLATE utf8mb4_unicode_ci,
  `relayOutput` text COLLATE utf8mb4_unicode_ci,
  `sensorFault` text COLLATE utf8mb4_unicode_ci,
  `sensorStatus` tinyint(1) NOT NULL DEFAULT '1',
  `notificationStatus` tinyint(1) NOT NULL DEFAULT '1',
  `hooterRelayStatus` tinyint(1) NOT NULL DEFAULT '1',
  `audioDecibelLevel` text COLLATE utf8mb4_unicode_ci,
  `relayMessage` varchar(225) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deviceReadStatus` tinyint(1) NOT NULL DEFAULT '0',
  `memoryAddressValue` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criticalRefMinValue` text COLLATE utf8mb4_unicode_ci,
  `criticalRefMaxValue` text COLLATE utf8mb4_unicode_ci,
  `warningRefMinValue` text COLLATE utf8mb4_unicode_ci,
  `warningRefMaxValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeRefMinValue` text COLLATE utf8mb4_unicode_ci,
  `outofrangeRefMaxValue` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `branch_id` (`branch_id`),
  KEY `facility_id` (`facility_id`),
  KEY `building_id` (`building_id`),
  KEY `sensors_ibfk_7` (`floor_id`),
  KEY `sensors_ibfk_8` (`lab_id`),
  KEY `categoryId` (`categoryId`),
  KEY `deviceId` (`deviceId`),
  KEY `sensorName` (`sensorName`),
  KEY `sensorCategoryId` (`sensorCategoryId`),
  CONSTRAINT `sensorsOLD_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_10` FOREIGN KEY (`deviceId`) REFERENCES `devices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_11` FOREIGN KEY (`sensorName`) REFERENCES `sensor_units` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_12` FOREIGN KEY (`sensorCategoryId`) REFERENCES `sensor_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_3` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_4` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_7` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_8` FOREIGN KEY (`lab_id`) REFERENCES `lab_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sensorsOLD_ibfk_9` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO sensorsOLD VALUES
("14","A-TEST","4","3","4","2","2","3","1","AQMI","3","Light","3","Indoor Device-01","11","NO2","","Digital","","Test Sen","","3","1","","","","","55","1","100","45","0","1","Priority","25","75","Both","critical value is low","critical value is high","40","60","Both","warning value is low","warning value is high","10","90","Both","OUT OF RANGE VALUE IS LOW","OUT OF RANGE VALUE IS HIGH","Low","low alert","","2022-04-08 23:09:41","2022-10-14 12:01:20","","","","","","","","","","","","","","0","","00:00:00","ppm","500000","","","13:30:00","ppm","500000","","Latch","","","","1","1","0","101","DISABLED","1","650","25","75","40","60","10","90"),
("22","A-TEST","4","3","4","2","2","3","1","AQMI","2","Gases","3","Indoor Device-01","3","O2 Sensor","Double","Modbus","TCP","modbus tester","13","3","1","192.255.255.255","Centigrade","192.168.5.50","32 Bit","55","1","100","45","0","1","Priority","25","75","Both","critical value is low","critical value is high","40","60","Both","warning value is low","warning value is high","10","85","Both","OUT OF RANGE VALUE IS LOW","OUT OF RANGE VALUE IS HIGH","","","","2022-04-14 23:45:02","2022-07-30 22:29:54","","","","","","","","","","","","","","0","","00:00:00","ppm","500000","","","13:30:00","ppm","500000","","Latch","","ON","","1","1","0","97","DISABLED","1","1010","25","75","40","60","10","85");




CREATE TABLE `server_usage_statitics` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` varchar(191) NOT NULL,
  `time` varchar(191) NOT NULL,
  `perc_memory_usage` varchar(191) NOT NULL,
  `avg_cpu_load` varchar(191) NOT NULL,
  `perc_server_load` varchar(191) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1889 DEFAULT CHARSET=latin1;


INSERT INTO server_usage_statitics VALUES
("28","2022-10-08","14:34:45","8.2 GiB / 15.51 GiB (52.87823284766%)","0.26","0","2022-10-08 09:04:46","2022-10-08 09:04:46"),
("29","2022-10-08","14:34:52","8.2 GiB / 15.51 GiB (52.865641808545%)","0.22","0","2022-10-08 09:04:53","2022-10-08 09:04:53");




CREATE TABLE `server_utilizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date_t` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_t` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perc_memory_usage` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avg_cpu_load` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perc_server_load` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `simulatorRunning` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `SimulatorName` varchar(225) DEFAULT NULL,
  `deviceId` varchar(225) DEFAULT NULL,
  `status` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;


INSERT INTO simulatorRunning VALUES
("5","DEFAULTSIMULATOR","","0"),
("6","SIMULATOR1","125","1");




CREATE TABLE `twa_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(225) NOT NULL,
  `parameterName` varchar(200) NOT NULL,
  `twaValue` varchar(200) NOT NULL,
  `resetDateTime` datetime DEFAULT NULL,
  `status` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=latin1;


INSERT INTO twa_info VALUES
("161","133","283","61.244001706444","2022-11-07 14:56:02",""),
("162","133","282","89.873334884644","2022-11-07 14:56:02","");




CREATE TABLE `user_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userEmail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16257 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO user_logs VALUES
("1","Admin","abhishekshenoy7@gmail.com","A-TEST","LoggedOut","2022-04-27 21:47:34","2022-04-27 21:47:34"),
("2","AIDEA Labs","info@aidealabs.com","AideaLab","LoggedOut","2022-04-27 21:53:40","2022-04-27 21:53:40");




CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobileno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employeeId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `facility_id` bigint(20) unsigned DEFAULT NULL,
  `building_id` bigint(20) unsigned DEFAULT NULL,
  `floor_id` bigint(20) unsigned DEFAULT NULL,
  `lab_id` bigint(20) unsigned DEFAULT NULL,
  `user_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changePassword` tinyint(1) NOT NULL DEFAULT '1',
  `sec_level_auth` int(11) NOT NULL DEFAULT '1',
  `otpno` int(11) NOT NULL DEFAULT '0',
  `otpgenerated_at` timestamp NULL DEFAULT NULL,
  `isverified` tinyint(1) NOT NULL DEFAULT '0',
  `login_fail_attempt` int(11) NOT NULL DEFAULT '0',
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `companyLogo` text COLLATE utf8mb4_unicode_ci,
  `empNotification` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_ativity` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `branch_id` (`branch_id`),
  KEY `facility_id` (`facility_id`),
  KEY `building_id` (`building_id`),
  KEY `floor_id` (`floor_id`),
  KEY `lab_id` (`lab_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_4` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_5` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_6` FOREIGN KEY (`lab_id`) REFERENCES `lab_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO users VALUES
("4","Ai-DEA Labs Pvt. Ltd","jananes@aidealabs.com","9449026060","0000","Ai-DEA Labs Pvt. Ltd","","","","","","","superAdmin","0","0","9471","2022-04-18 21:26:32","0","0","0","","$2y$10$IyUWe7MX9BMfz/7pp5XChezT1rRJlfcVWl9VXwPXQcxm75oNgv.ie","","SuperAdmin/logo/Janane_Logo.png","1","2022-03-31 20:13:04","2022-12-29 17:52:00","2022-12-29 17:52:00"),
("5","Abhishek","abhishekshenoy7@gmail.com","8660839819","00000","A-TEST","","","","","","","systemSpecialist","0","0","7231","2022-11-19 12:34:36","1","0","0","","$2y$10$5D7HJuAOmIROyMpW4Ef/Cu3j3123zkCrDMYE8ikdEoi18QdTysQ2.","","","1","2022-03-31 21:20:55","2022-12-30 12:56:46","2022-12-30 12:56:46");




CREATE TABLE `vendors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companyCode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendorName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contactPerson` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO vendors VALUES
("1","A-TEST","Arun P","8956895689","arunp@gmail.com","mangalore","Arun Prakash","2022-04-01 19:37:24","2022-04-04 17:10:48"),
("3","PR-TEST","Vikranth","9685968590","vikranth@gmail.com","Bajpe","Vikranth","2022-04-04 17:07:06","2022-04-04 17:07:38");


