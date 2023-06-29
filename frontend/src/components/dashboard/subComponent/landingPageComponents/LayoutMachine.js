import React, { useState } from 'react';
import Grid from '@mui/material/Grid';
import MachineCard from './MachineCard';
import DigitalCard from './DigitalCard';
import { Warning } from '@mui/icons-material';
import { Backdrop, Button, CircularProgress } from '@mui/material';

function LayoutMachine({
  setOpen, analogSensorList, digitalSensorList, modbusSensorList, setSensorTagId, setSensorTag, sensorIdList, sensorListVisibility
}) {
  const [open1, setOpen1] = useState(true);

  const handleClose  = () => {
    setOpen1(false);
  };

  const handleToggle = () => {
    setOpen1(oldValue => !oldValue);
  };

  return (
    <div
      style={{
        marginTop: 5,
        maxHeight: '65vh',
        overflow: 'auto',
        padding: 5,
      }}
    >
      {/* <Button onClick={handleToggle}>Show backdrop</Button> */}
      <Grid
        container
        spacing={2}
        style={{ padding: 1 }}
      >
        {sensorListVisibility === true && (
          <div style={{
            background: 'gray',
            height: '67%',
            marginLeft: '10px',
            width: '-webkit-fill-available',
            position: 'absolute',
            zIndex: 1,
            opacity: 0.8,
            display: 'flex',
            flexDirection: 'row',
            alignItems: 'center',
            justifyContent: 'center',
          }}>
            <Backdrop
              sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 1, position: 'relative', width: '100%', height: '100%' }}
              style={{
                display: 'flex',
                alignItems: 'center',
                alignContent: 'center',
                flexDirection: 'row',
                flexWrap: 'wrap',
                justifyContent: 'center'
              }}
              open={sensorListVisibility}
              // onClick={handleClose}
            >
              <Warning color='amber' style={{
                fontSize: '70px',
                color: 'yellow',
              }} />
              <span style={{
                color: 'white',
                fontWeight: 'bold',
                fontSize: '25px'
              }}>
                Device is disconnected
              </span> 
              
            </Backdrop>
            {/* <div style={{
              display: 'flex',
              alignItems: 'center',
              flexDirection: 'row',
              flexWrap: 'wrap',
              justifyContent: 'center'
            }}>
              <Warning color='amber' style={{
                fontSize: '70px',
                color: 'yellow',
              }} />
              <span style={{
                color: 'white',
                fontWeight: 'bold',
                fontSize: '25px'
              }}>
                Device got disconnected
              </span> 
            </div> */}
          </div>
        )}
        {analogSensorList.map((data) => {
          return (
            <Grid item xs={12} sm={6} md={4} lg={3} xl={3} key={data.sensorTagId}>
              <MachineCard
                setOpen={setOpen}
                id={data.sensorTagId}
                sensorStatus={data.sensorStatus}
                sensorName={data.sensorTag}
                sensorNameUnit={data.sensorNameUnit}
                min={data.min}
                max={data.max}
                avg={data.avg}
                last={data.last}
                alertColor={data.alertColor}
                setSensorTagId={setSensorTagId}
                setSensorTag={setSensorTag}
                color={data.alertColor}
                lightColor={data.alertLightColor}
                maxRatedReadingScale={data.maxRatedReadingScale}
                minRatedReadingScale={data.minRatedReadingScale}
                units={data.units}
                sensorIdList={sensorIdList}
              />
            </Grid>
          );
        })}
        {digitalSensorList.map((data) => {
          return (
            <Grid item xs={12} sm={6} md={4} lg={3} xl={3} key={data.sensorTagId}>
              <DigitalCard
                id={data.sensorTagId}
                sensorStatus={data.sensorStatus}
                sensorName={data.sensorTag}
                sensorNameUnit={data.sensorNameUnit}
                min={data.min}
                max={data.max}
                avg={data.avg}
                last={data.last}
                alertColor={data.alertColor}
                setSensorTagId={setSensorTagId}
                setSensorTag={setSensorTag}
                color={data.alertColor}
                lightColor={data.alertLightColor}
                maxRatedReadingScale={data.maxRatedReadingScale}
                minRatedReadingScale={data.minRatedReadingScale}
                units={data.units}
                sensorIdList={sensorIdList}
              />
            </Grid>
          );
        })}
        {modbusSensorList.map((data) => {
          return (
            <Grid item xs={12} sm={6} md={4} lg={3} xl={3} key={data.sensorTagId}>
              <MachineCard
                setOpen={setOpen}
                id={data.sensorTagId}
                sensorStatus={data.sensorStatus}
                sensorName={data.sensorTag}
                sensorNameUnit={data.sensorNameUnit}
                min={data.min}
                max={data.max}
                avg={data.avg}
                last={data.last}
                alertColor={data.alertColor}
                setSensorTagId={setSensorTagId}
                setSensorTag={setSensorTag}
                color={data.alertColor}
                lightColor={data.alertLightColor}
                maxRatedReadingScale={data.maxRatedReadingScale}
                minRatedReadingScale={data.minRatedReadingScale}
                units={data.units}
                sensorIdList={sensorIdList}
              />
            </Grid>
          );
        })}
      </Grid>
    </div>
  );
}

export default LayoutMachine;
