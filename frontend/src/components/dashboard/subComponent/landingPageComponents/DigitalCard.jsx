import React from 'react';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import Grid from '@mui/material/Grid';
import Typography from '@mui/material/Typography';
import Tooltip from '@mui/material/Tooltip';
import { CardActionArea } from '@mui/material';
import Stack from '@mui/material/Stack';
import { styled } from '@mui/material/styles';
import Switch from '@mui/material/Switch';
import FormControlLabel from '@mui/material/FormControlLabel';
import { useEffect, useState } from 'react';

const MaterialUISwitch = styled(Switch)(({ theme }) => ({
  width: 62,
  height: 34,
  padding: 7,
  '& .MuiSwitch-switchBase': {
    margin: 1,
    padding: 0,
    transform: 'translateX(6px)',
    '&.Mui-checked': {
      color: '#fff',
      transform: 'translateX(22px)',
      '& .MuiSwitch-thumb:before': {
        /* eslint-disable-next-line */
        backgroundImage: `url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 24 24"><path fill="${encodeURIComponent(
          theme.palette.getContrastText(theme.palette.primary.main),
        )}" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/></svg>')`,
      },
      '& + .MuiSwitch-track': {
        opacity: 1,
        backgroundColor: theme.palette.mode === 'dark' ? '#8796A5' : '#aab4be',
      },
    },
  },
  '& .MuiSwitch-thumb': {
    backgroundColor: theme.palette.mode === 'dark' ? '#003892' : '#001e3c',
    width: 32,
    height: 32,
    '&:before': {
      content: '\'\'',
      position: 'absolute',
      width: '100%',
      height: '100%',
      left: 0,
      top: 0,
      backgroundRepeat: 'no-repeat',
      backgroundPosition: 'center',
      /* eslint-disable-next-line */
      backgroundImage: `url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 24 24"><path fill="${encodeURIComponent(
        theme.palette.getContrastText(theme.palette.primary.main),
      )}" d="M19,13H5V11H19V13Z" /></svg>')`,
    },
  },
  '& .MuiSwitch-track': {
    opacity: 1,
    backgroundColor: theme.palette.mode === 'dark' ? '#8796A5' : '#aab4be',
    borderRadius: 20 / 2,
  },
}));

function DigitalCard(props) {
  const [sensorStatus, setSensorStatus] = useState(props.sensorStatus || 0);

  useEffect(()=>{
    setSensorStatus(props.sensorStatus || 0);
  }, [props.sensorStatus]);

  return (
    <Card
      sx={{ minWidth: 200, boxShadow: 5, borderRadius: 2, height: 217 }}
    >
      <CardActionArea onClick={() => {
          sensorStatus === 0 ? '' : '';
        }}
        style={{
          cursor: 'not-allowed',
        }}
      >
        <Grid item xs={12} style={{ backgroundColor: sensorStatus === 0 ? '#9e9e9e' : props.lightColor || '#cce6ff', height: '50px' }}>
          <Stack
            direction="row"
            justifyContent="space-evenly"
            alignItems="center"
            spacing={1}
          >
            <Tooltip title={props.sensorNameUnit}>
              <Typography style={{
                color: sensorStatus === 0 ? '#212121' : props.color || '#004d99',
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
                color: sensorStatus === 0 ? '#212121' : props.color || '#004d99',
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
          </Stack>
        </Grid>
        <Box sx={{ width: '100%', borderRadius: '8' }} style={{
          height: '166px',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center',
        }}>
          <Stack
            direction="row"
            justifyContent="space-evenly"
            alignItems="flex-start"
            spacing={5}
            mt={2}
            xs={{ justifyContent: 'space-around' }}
            style={{
              marginTop: '0px'
            }}
          >
            <div style={{
              width: 90, height: 90, float: 'left', marginTop: 2,
              display: 'flex'
            }}
            >
              <FormControlLabel
                control={<MaterialUISwitch  checked={sensorStatus === 1 ? true : false}  disabled style={{
                  cursor: 'not-allowed',
                }}/>}
                label="Digital"
                style={{
                  cursor: 'not-allowed',
                }}
              />
            </div>
          </Stack>
          <Stack
            direction="row"
            justifyContent="space-evenly"
            alignItems="flex-end"
            spacing={1}
          />
        </Box>
      </CardActionArea>
    </Card>
  );
}

export default DigitalCard;
