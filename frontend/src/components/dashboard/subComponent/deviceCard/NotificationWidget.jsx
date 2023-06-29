import './notificationWidget.scss';
import {
  VolumeUp,
  Sensors,
  AccessTime,
  NotificationsActiveOutlined,
  SensorsOff,
  VolumeOff,
  Campaign,
} from '@mui/icons-material';
import { useEffect, useState } from 'react';
import { setAQIColor } from '../../../../utils/helperFunctions';
import MachineCircularProgressbar from '../landingPageComponents/MachineCircularProgressbar';
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
function NotificationWidget({ type, figure, handleClick, userRole, testHooter, aqi }) {
  let data;
  const [dateTime, setDateTime] = useState({
    time: '',
    date: '',
  });
  switch (type) {
  case 'hooterStatus':
    data = {
      title: 'Hooter',
      figure: figure !== 1 ? 
      (<VolumeOff style={{fontSize: '75px', color : '#808080'}}/>) :
      (<VolumeUp style={{fontSize: '75px', color : 'goldenrod', animation: 'flash 1s infinite '}}/>) , 
      link: '',
      icon: userRole === 'systemSpecialist' 
        // ||  userRole === 'Admin' 
        // ||  userRole === 'Manager' 
        ? 
        (
          <Campaign
            className="icon"
            style={{ backgroundColor: 'rgba(179, 157, 219, 0.5)', color: '#512da8' }}
          />
        )
      : ''
      ,
    };
    break;
  case 'disconnectedDevice':
    data = {
      title: 'Disconnected Devices',
      link: '',
      figure: figure || 0,
      icon: (
        <SensorsOff
          className="icon"
          style={{
            backgroundColor: 'rgba(255, 0, 0, 0.2)',
            color: 'crimson',
          }}
        />
      ),
    };
    break;
  case 'devices':
    data = {
      title: 'Total Devices',
      link: '',
      figure: figure || 0,
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
      figure: figure || 0,
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
  case 'time':
    data = {
      title: 'Time',
      link: '',
      figure: dateTime.time,
      icon: (
        <AccessTime
          className="icon"
          style={{
            backgroundColor: '#e1f5fe',
            color: '#0288d1',
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
              // text={parseFloat(aqi).toFixed(2)}
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
        };
      });
    });
  }, []);

  return (
    <div
      className="widget"
      onClick={() => {
        type === 'alerts' && handleClick();
      }}
      style={{ cursor: type === 'alerts' && 'pointer', 
      display: 'inline-block',
      justifyContent: type === 'hooterStatus' && (userRole !== 'systemSpecialist' 
        // || userRole !== 'Manager'
        ) ? 'center' : '' 
    }}
    >
      { type !== 'hooterStatus' ? 
        <div>
          <div className="left">
            <span className="title" style={{minWidth: type === 'disconnectedDevice' ? '150px' : '120px'}}>{data.title}</span>
            <span className="counter" style={{minWidth: type === 'disconnectedDevice' ? '150px' : '120px', alignSelf: type === 'aqi' && 'center'}}>
              {data.figure}
            </span>
            <span className="link">{data.link}</span>
          </div>
          <div className="right" style={{
            alignItems: 'end'
          }}>
            <div className="percentage positive">
            </div>
            <div onClick={()=>{
              // type === 'hooterStatus' && (userRole === 'systemSpecialist' || 'Admin')  ? testHooter() : {};
              }}
            >
              {data.icon}
            </div>
          </div>
        </div> : 
        <div>
          <div className="left">
            <span className="title" style={{minWidth: '150px'}}>{data.title}</span>
          </div>
          <div style={{
            minWidth: '150px'
          }}>
            <div style={{
              height: '100%',
              display: 'flex',
              minWidth: '150px',
              alignItems: 'flex-end',
              justifyContent: userRole === 'systemSpecialist' 
                // || userRole === 'Admin' 
                // ||  userRole === 'Manager' 
                ? 'space-between' : 'center',
            }}>
              <div 
                onClick={()=>handleClick()}
                style={{
                  cursor: (userRole === 'systemSpecialist' 
                  || userRole === 'Admin' 
                  || userRole === 'Manager'
                  )  && 'pointer'
                }}
              >
                <span className="counter" style={{minWidth: '150px'}}>
                  {data.figure}
                </span>
              </div>
              <div 
                style={{
                  float: 'right',
                  height: '100%',
                  cursor: 'pointer'
                }} 
                onClick={()=>{
                  (userRole === 'systemSpecialist' || userRole === 'Manager' || userRole === 'Admin') && testHooter();
                }}
              >
                <div className="right" style={{
                  alignItems: 'end'
                }}>
                  {data.icon}
                </div>
              </div>

            </div>
          </div>
        </div>
      }
    </div>
  );
}

export default NotificationWidget;
