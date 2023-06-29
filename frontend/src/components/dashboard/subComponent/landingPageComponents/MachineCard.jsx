import React, { useEffect, useState } from 'react';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import Grid from '@mui/material/Grid';
import Typography from '@mui/material/Typography';
import Tooltip from '@mui/material/Tooltip';
import { CardActionArea } from '@mui/material';
import Stack from '@mui/material/Stack';
import MachineCircularProgressbar from './MachineCircularProgressbar';
import { getSensorBackgroundColor, getSensorHeaderColor, setAlertPriorityAndType, setAlertStatusCode } from '../../../../utils/helperFunctions';
import { WifiOffOutlined } from '@mui/icons-material';

function MachineCard(props) {
  const [alertStatus, setAlertStatus] = useState(4);

  useEffect(() => {
    let element = {
      alertLabel: 'Good',
      alertColor: 'green',
      alertPriority: 4,
    };

    const alertObject = props.sensorIdList?.filter((alert) => {
      return parseInt(props.id) === parseInt(alert.id);
    });

    alertObject?.map((data) => {
      setAlertStatusCode(element, data, setAlertStatus);
      element = setAlertPriorityAndType(element, data);
    });

  }, []);

  const handleClick = () => {
    props.setSensorTagId(props.id);
    props.setSensorTag(props.sensorName);
    props.setOpen(true);
  }
  return (
    <Card 
      sx={{ minWidth: 200, boxShadow: 5, borderRadius: 2 }}
    >
      <CardActionArea
        onClick={() => {
          props.sensorStatus === 0 ? '' : handleClick();
        }}
        style={{
          cursor: props.sensorStatus === 0 ? 'not-allowed' : 'pointer',
        }}
      >
        <Grid
          item xs={12}
          style={{
            // backgroundColor: getSensorBackgroundColor(props.sensorStatus, alertStatus), //props.lightColor || '#cce6ff', 
            backgroundColor: props.sensorStatus === 0 ? '#9e9e9e' : props.lightColor || '#a5d6a7',
            height: '50px'
          }}>
          {/* <Stack
            direction="row"
            justifyContent="space-evenly"
            alignItems="center"
            spacing={1}
          >
            <Tooltip title={props.sensorNameUnit}>
              <Typography style={{
                // color: getSensorHeaderColor(props.sensorStatus, alertStatus) || '#004d99',
                color: props.sensorStatus === 0 ? '#212121' : props.alertColor || '#004d99',
                marginTop: '15px',
                whiteSpace: 'nowrap',
                width: '100px',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                fontWeight: '500',
              }}
              >
                {props.sensorNameUnit}
              </Typography>
            </Tooltip>
            <Tooltip title={props.sensorName}>
              <Typography style={{
                // color: getSensorHeaderColor(props.sensorStatus, alertStatus) || '#004d99',
                color: props.sensorStatus === 0 ? '#212121' : props.alertColor || '#004d99',
                marginTop: '15px',
                whiteSpace: 'nowrap',
                width: '100px',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                fontWeight: '500',
                fontSize: '20px',
              }}
              >
                {props.sensorName}
              </Typography>
            </Tooltip>
          </Stack> */}
          <Box style={{
            padding: '10px'
          }}>
            <Tooltip title={props.sensorNameUnit}>
              <span style={{
                fontSize: '18px',
                float: 'left',
                // color: getSensorHeaderColor(props.sensorStatus, alertStatus) || '#004d99',
                color: props.sensorStatus === 0 ? '#212121' : props.alertColor || '#004d99',
                // marginTop: '15px',
                whiteSpace: 'nowrap',
                // width: '100px',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                // fontWeight: '500',
                fontWeight: 'bold',
                maxWidth: '50%',
              }}
              >
                {props.sensorNameUnit}
              </span>
            </Tooltip>
            <Tooltip title={props.sensorName}>
              <span style={{
                // color: getSensorHeaderColor(props.sensorStatus, alertStatus) || '#004d99',
                color: props.sensorStatus === 0 ? '#212121' : props.alertColor || '#004d99',
                float: 'right',
                fontSize: '18px',
                fontWeight: 'bold',
                // marginRight: 5,
                // fontWeight: 500,
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                // width: '50%',
                // width: '120px',
                maxWidth: '50%',
                whiteSpace: 'nowrap',
              }}
              >
                {props.sensorName}
              </span>
            </Tooltip>
          </Box>
        </Grid>
        <Box sx={{ width: '100%', borderRadius: '8' }}>
          <Stack
            direction="row"
            justifyContent="space-evenly"
            alignItems="flex-start"
            spacing={2}
            mt={2}
            xs={{ justifyContent: 'space-around' }}
          >
            <div style={{
              width: 90, height: 90, float: 'left', marginTop: 2,
            }}
            >
              {props.sensorStatus === '0' ? <WifiOffOutlined style={{ fontSize: '70px', color: '#707070' }} /> :
                <MachineCircularProgressbar 
                  score={props.sensorStatus === 0 ? '0': props.last}
                  text={props.sensorStatus === 0 ? 'NA' : props.last}
                  color={props.sensorStatus === 0 ? 'NA' : props.alertColor} 
                  minReading={props.minRatedReadingScale || 0} 
                  maxReading={props.maxRatedReadingScale || 100} />
              }
            </div>
            <div style={{
              width: 90, height: 90, float: 'left', marginTop: 2,
            }}
            >
              <Typography style={{ marginLeft: 0, color: '#004d99' }} align="left" display="block" gutterBottom component="div" />
              <Typography
                align="left"
                display="block"
                gutterBottom
                component="div"
                style={{
                  fontWeight: 1000, 
                  color: props.sensorStatus === 0 ? '#212121' : props.color || '#7F8487', 
                  marginLeft: 9, 
                  marginTop: 22,
                }}
              >
                {props.units}
              </Typography>
            </div>
          </Stack>
          <Stack
            direction="row"
            justifyContent="space-evenly"
            alignItems="flex-end"
            spacing={1}
          >
            <div>
              <Typography style={{ color: '#004d99', textAlignLast: 'center' }} align="left" display="block" gutterBottom component="div">
                <b>MIN</b>
              </Typography>
              <Typography
                align="left"
                display="block"
                gutterBottom
                component="div"
                style={{ fontWeight: 600, color: '#004d99' || '#7F8487', textAlignLast: 'center'}}
              >
                {props.sensorStatus === 0 ? 'NA' : props.min}
              </Typography>
            </div>
            <div>
              <Typography style={{ color: '#004d99', textAlignLast: 'center' }} align="left" display="block" gutterBottom component="div">
                <b>MAX</b>
              </Typography>
              <Typography
                align="left"
                display="block"
                gutterBottom
                component="div"
                style={{ fontWeight: 600, color: "#004d99" || '#7F8487', textAlignLast: 'center'}}
              >
                {props.sensorStatus === 0 ? 'NA' : props.max}
              </Typography>
            </div>
            <div>
              <Typography style={{ color: '#004d99',textAlignLast: 'center' }} align="center" display="block" gutterBottom component="div">
                <b>AVG</b>
              </Typography>
              <Typography
                align="center"
                display="block"
                gutterBottom
                component="div"
                style={{ fontWeight: 600, color: "#004d99" || '#7F8487', textAlignLast: 'center' }}
              >
                {props.sensorStatus === 0 ? 'NA' : props.avg}
              </Typography>
            </div>
          </Stack>
        </Box>
      </CardActionArea>
    </Card>
  );
}

export default MachineCard;
