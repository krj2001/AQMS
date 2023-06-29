import './widget.scss';
import {
  KeyboardArrowUp,
  Groups,
  DeviceThermostat,
  Science,
  Sensors,
  AccessTime,
  NotificationsActiveOutlined,
} from '@mui/icons-material';
import { useEffect, useState } from 'react';
import MachineCircularProgressbar from '../dashboard/subComponent/landingPageComponents/MachineCircularProgressbar';
import { setAQIColor } from '../../utils/helperFunctions';

function Widget({ type , setAlertOpen, totalSensors, totalAlerts, aqi }) {
  let data; 

  const [dateTime, setDateTime] = useState({
    time: '',
    date: '',
  });
  switch (type) {
  // case 'user':
  //   data = {
  //     title: 'AQMS Users',
  //     figure: 45,
  //     link: 'See all users',
  //     diff: '20%',
  //     icon: (
  //       <Groups
  //         className="icon"
  //         style={{
  //           color: 'crimson',
  //           backgroundColor: 'rgba(255, 0, 0, 0.2)',
  //         }}
  //       />
  //     ),
  //   };
  //   break;
  case 'labs':
    data = {
      title: 'Labs under your location',
      link: '',
      figure: 8,
      diff: '30%',
      icon: (
        <Science
          className="icon"
          style={{
            backgroundColor: 'rgba(218, 165, 32, 0.2)',
            color: 'goldenrod',
          }}
        />
      ),
    };
    break;
  case 'devices':
    data = {
      title: 'Total Sensors',
      link: '',
      figure: totalSensors,
      diff: '40%',
      icon: (
        <Sensors
          className="icon"
          style={{ backgroundColor: 'rgba(0, 128, 0, 0.2)', color: 'green' }}
        />
      ),
    };
    break;
  case 'alerts':
    data = {
      title: 'Active Alerts',
      link: '',
      figure: totalAlerts,
      diff: '50%',
      icon: (
        <NotificationsActiveOutlined
          className="icon"
          style={{
            backgroundColor: 'rgba(128, 0, 128, 0.2)',
            color: 'purple',
          }}
        />
      ),
    };
    break;
    case 'aqi':
    data = {
      title: 'AQI',
      link: '',
      figure: (
        <div style={{
          width: '50%',
          minWidth: '100px',
          maxWidth: '100px',
          height: '50%',
          maxHeight: '50px'
        }}>
            <MachineCircularProgressbar 
              text={aqi}
              score={aqi} 
              color={setAQIColor(aqi)} 
              minReading='0' 
              maxReading='500' 
            />
        </div>
      ),
      diff: '',
      icon: '',
    };
    break;
  case 'time':
    data = {
      title: 'Time',
      link: '',
      figure: dateTime,
      diff: '',
      icon: (
        <AccessTime
          className="icon"
          style={{
            backgroundColor: '#e1f5fe',
            color: '#0288d1'
          }}
        />
      ),
    };
    break;
  default:
    break;
  }
  useEffect(() => {
    setInterval(() => {
      const currentTime = new Date();
      setDateTime(() => {
        return {
          time: currentTime.toLocaleTimeString('en', {
            hour: 'numeric', hour12: true, minute: 'numeric', second: 'numeric',
          }),
          date: currentTime.toLocaleDateString('es-CL')
        };
      });
    });
  }, []);
  
  return (
    <div className="widget" onClick={() => {
        (type === 'alerts' || type === 'aqi') && setAlertOpen(true);
      }}
      style={{ 
        minWidth: type === 'time' && '195px',
        cursor: type === 'alerts' && 'pointer', 
        display: type === "aqi" && aqi === 'NA' ? 'none' : 'inline-block' }}
    >
      <div style={{
        display: type === 'time' && 'flex',
        justifyContent: type === 'time' && 'space-between'
      }}>
        <div className="left" >
          <span className="title" style={{minWidth: '150px'}}>{data.title}</span>
          {type === 'time' ? <>
              <span className="counter" style={{minWidth: '150px', lineHeight: 1.4}}>
                {data.figure.time}
              </span>
              <span className="counter" style={{minWidth: '150px', lineHeight: 1.2}}>
                {data.figure.date}
              </span>
          </>
            :
            <span className="counter" style={{minWidth: '130px', alignSelf: type === 'aqi' && 'center'}}>
              {data.figure}
            </span>
          }
          <span className="link">{data.link}</span>
        </div>
        <div className="right">
          <div className="percentage positive">
          </div>
          {data.icon}
        </div>
      </div>
    </div>
  );
}

export default Widget;
