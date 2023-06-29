import React, { useState, useEffect } from 'react';
import { Backdrop, Button, CircularProgress } from '@mui/material';
import { ArrowBack } from '@mui/icons-material';
import Widget from '../../../widget/Widget';
import LayoutMachine from '../landingPageComponents/LayoutMachine';
import SensorGraphComponent from '../landingPageComponents/SensorGraphComponent';
import { DashboardSensorListDetails } from '../../../../services/LoginPageService';
import AlertModalComponent from '../landingPageComponents/AlertModalComponent';
import ApplicationStore from '../../../../utils/localStorageUtil';
import AQITrendModal from '../../../reportSectionComponents/AQITrendModal';
function LandingPageComponent({ locationDetails, setIsDashBoard }) {
  const [deviceId, setDeviceId] = useState({
    device_id: locationDetails.device_id
  });
  const [open, setOpen] = useState(false);
  const [alertOpen, setAlertOpen] = useState(false);
  const [analogSensorList, setAnalogSensorList] = useState([]);
  const [digitalSensorList, setDigitalSensorList] = useState([]);
  const [modbusSensorList, setModbusSensorList] = useState([]);
  const [sensorListVisibility, setSensorListVisibility] = useState(false);
  const [sensorTagId, setSensorTagId] = useState('');
  const [sensorTag, setSensorTag] = useState('');
  const [segretionInterval, setSegretionInterval] = useState('1');
  const [rangeInterval, setRangeInterval] = useState('1440');
  const [totalSensors, setTotalSensors] = useState(0);
  const [totalAlerts, setTotalALerts] = useState(0);
  const [aqiIndex, setAqiIndex] = useState('NA');
  const { intervalDetails } = ApplicationStore().getStorage('userDetails');
  const { sensorIdList } = ApplicationStore().getStorage('alertDetails');
  const intervalSec = intervalDetails.sensorLogInterval * 1000;
  const [backdropOpen, setBackdropOpen] = useState(true);
  const [aqiTrendOpen, setAQITrendOpen] = useState(false);
  /* eslint-disable-next-line */
  useEffect(() => {
    intervalCallFunction();
    /* eslint-disable-next-line */
    if (open === false) {
      const devicePolling = setInterval(() => {
        intervalCallFunction();
      }, intervalSec);
      return () => {
        clearInterval(devicePolling);
      };
    }
  }, [locationDetails, open]);

  const intervalCallFunction = () => {
    DashboardSensorListDetails({ device_id: locationDetails.device_id }, fetchSenosorListSuccess, fetchSenosorListException);
  };
  const fetchSenosorListSuccess = (dataObject) => {
    setTotalSensors(dataObject?.sensorCount || '0');
    setTotalALerts(dataObject?.alertCount || '0');
    setAqiIndex(dataObject?.aqiIndex.replaceAll(",", "") || 'NA');
    setAnalogSensorList(dataObject?.Analog?.data || []);
    setDigitalSensorList(dataObject?.Digital?.data || []);
    setModbusSensorList(dataObject?.Modbus?.data || []);
    setBackdropOpen(false);
    // Test Device disconnected Status 
    dataObject?.disconnectedStatus === '1' ? setSensorListVisibility(true) : setSensorListVisibility(false);
  };

  const fetchSenosorListException = () => {
  };

  return (
    <div style={{ textAlignLast: 'left' }}>
      <Button
        variant="outlined"
        style={{ marginLeft: '10px', marginTop: '5px' }}
        startIcon={<ArrowBack />}
        onClick={() => {
          setIsDashBoard(2);
        }}
      >
        Back to Data Logger
      </Button>
      <div className="widgets" style={{ height: 'auto', backgroundColor: '#fafafa', padding: 10 }}>
        <div className="widgets" style={{ 
          height: 'auto', backgroundColor: '#fafafa', padding: 10,
          display: 'flex',
          flexDirection: 'row',
          flexWrap: 'wrap',
          width: '100%'
        }}>
          <Widget type="devices" totalSensors={totalSensors} />
          <Widget type="alerts" setAlertOpen={setAlertOpen} totalAlerts={totalAlerts} />
          <Widget type="aqi" setAlertOpen={setAQITrendOpen} aqi={aqiIndex}/>
          <Widget type="time"/>
        </div>
      </div>
      <LayoutMachine
        setOpen={setOpen}
        analogSensorList={analogSensorList}
        digitalSensorList={digitalSensorList}
        modbusSensorList={modbusSensorList}
        sensorIdList={sensorIdList}
        setSensorTagId={setSensorTagId}
        setSensorTag={setSensorTag}
        sensorListVisibility={sensorListVisibility}
      />

      <SensorGraphComponent
        open={open}
        setOpen={setOpen}
        sensorTagId={sensorTagId}
        segretionInterval={segretionInterval}
        setSegretionInterval={setSegretionInterval}
        rangeInterval={rangeInterval}
        setRangeInterval={setRangeInterval}
        sensorTag={sensorTag}
      />
      <AlertModalComponent alertOpen={alertOpen} setAlertOpen={setAlertOpen} locationDetails={deviceId} />
      <AQITrendModal openTrend={aqiTrendOpen} setOpenTrend={setAQITrendOpen} id={{device_id:locationDetails.device_id}} type='sensor'/>
      <Backdrop
        sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 1 }}
        open={backdropOpen}
        // onClick={handleClose}
      >
        <CircularProgress color="inherit" />
      </Backdrop>
    </div>
  );
}

export default LandingPageComponent;
