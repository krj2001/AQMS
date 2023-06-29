import './deviceWidget.scss';
import {
  NotificationsActiveOutlined,
  WifiOffOutlined,
  WifiOutlined,
} from '@mui/icons-material';
import { Badge, Box, Chip } from '@mui/material';
import { useEffect, useState } from 'react';
import { getDeviceBackgroundColor, getDeviceHeaderColor, getDeviceModeColor, setAlertPriorityAndType, setAlertStatusCode } from '../../../../utils/helperFunctions';

function DeviceWidget({
  data, setLocationDetails, setBreadCrumbLabels, setIsDashBoard, deviceIdList
}) {
  const [modeColor, setModeColor] = useState('primary');
  const [alertStatus, setAlertStatus] = useState(6);

  useEffect(() => {
    if (data) {
      // setModeColor(getDeviceBackgroundColor(data.deviceMode, alertStatus, parseInt(data.disconnectedStatus)));
    }
    let element = {
      alertLabel: 'Good',
      alertColor: 'green',
      alertPriority: 6,
    };

    const alertObject = deviceIdList?.filter((alert) => {
      return data.id === parseInt(alert.id);
    });

    alertObject?.map((data) => {
      setAlertStatusCode(element, data, setAlertStatus);
      element = setAlertPriorityAndType(element, data);
    });
    console.log(deviceIdList);
    console.log(alertObject);
    console.log(alertStatus);
  }, []);

  const handleClick = () => {
    setLocationDetails((oldValue) => {
      return { ...oldValue, device_id: data.id };
    });
    setBreadCrumbLabels((oldvalue) => {
      return { ...oldvalue, deviceLabel: data.deviceName };
    });
    setIsDashBoard(1);
  };

  return (
    <div
      className="widget"
      onClick={() => {
        data.deviceMode !== 'enabled' || data.disconnectedStatus === '1' ? '' : handleClick(data);
      }}
      style={{
        height: '190px', cursor: data.deviceMode !== 'enabled' || data.disconnectedStatus === '1' ? 'not-allowed' :'pointer', display: 'block', padding: 1,
      }}
    >
      {/* <label>11111</label> */}
      <div
        className="left"
        style={{
          backgroundColor: getDeviceBackgroundColor(data.deviceMode, alertStatus, parseInt(data.disconnectedStatus)),
          borderTopRightRadius: '10px',
          borderTopLeftRadius: '10px',
          alignContent: 'space-between',
        }}
      >
        <Box 
        style={{
          // display: 'flex',
          // alignContent: 'center',
          // height: 40,
          // flexDirection: 'row',
          // justifyContent: 'space-between',
          // alignItems: 'center',
          // width: '100%'
          padding: '5px'
        }}
        sx={{
          width: '100%'
        }}
        >
          <Box sx={{
            // width: {xs: '50%', sm: '100%'}
          }}
          style={{
            // margin: '5px'
          }}
          >
            <span
              // className="title"
              style={{
                fontSize: '18px',
                float: 'left',
                // padding: '5px',
                // marginTop: 5,
                // marginLeft: 5,
                fontWeight: 'bold',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                // width: '50%',
                // minWidth: '120px',
                maxWidth: '50%',
                whiteSpace: 'nowrap',
                color: getDeviceHeaderColor(data.deviceMode, alertStatus, parseInt(data.disconnectedStatus)),
              }}
            >
              {data.deviceName}
            </span>
          </Box>
          <span
            // className="counter"
            style={{
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
              color: getDeviceHeaderColor(data.deviceMode, alertStatus, parseInt(data.disconnectedStatus)),
            }}
          >
            {data.deviceTag}
          </span>
        </Box>
        <span className="link">{data.link}</span>
      </div>
      <div className="right">
        <div
          className="percentage"
          style={{
            height: 150,
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'space-between',
            alignContent: 'center',
          }}
        >
          <div style={{
            height: data.deviceMode === 'disabled' ? '100%' : '70%',
            width: '100%',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
          }}
          >
            {data.disconnectedStatus === '1'
              ? (
                <div style={{
                  height: '100%', width: '100%', overflow: 'auto', display: 'flex', alignItems: 'center',
                }}
                >
                  <div style={{ width: '100%', alignContent: 'center', color: 'black' }}>
                      <WifiOffOutlined style={{ fontSize: '70px', color: '#707070' }} />
                  </div>
                </div>
              )
              : (
                <div style={{
                  width: '100%', height: '100%', overflow: 'auto', display: 'flex', alignItems: 'center',
                }}
                >
                  <div style={{ width: '60%', textAlignLast: 'center', paddingLeft: 10 }}>
                    Active Alarms :
                  </div>
                  <div style={{
                    width: '40%', alignContent: 'center', color: 'black', marginTop: 5,
                  }}
                  >
                    <Badge
                      badgeContent={data.alertDataCount}
                      style={{
                        // color: data.deviceMode === 'disabled' ? '#757575' : '#f44336',
                        // color: 'green',
                      }}
                      color={data.alertDataCount === '0' ? 'success' : 'error'}
                      max={999}
                    >
                      <NotificationsActiveOutlined
                        style={{ fontSize: '40px' }}
                        sx={{
                          color: data.deviceMode === 'disabled' ? '' : '#ffa000'
                        }}
                      />
                    </Badge>
                  </div>
                </div>
              )}
          </div>
          <div style={{
            height: '30%',
            width: '100%',
            display: 'flex',
            overflow: 'auto',
            alignItems: 'center',
            justifyContent: 'flex-end',
            marginRight: 15,
          }}
          >
            <div style={{
              textAlignLast: 'left', textAlign: 'justify', paddingLeft: 10, marginRight: 5,
            }}
            >
              Device Mode :
            </div>
            <div style={{
              alignContent: 'center', color: 'black', textAlignLast: 'right',
            }}
            >
              <Chip
                label={data.disconnectedStatus === '1' ? 'Disconnected': data.deviceMode}
                variant="outlined"
                sx={{
                  color:data.disconnectedStatus === '1' ? '#212121' : getDeviceModeColor(data.deviceMode),
                  borderColor:data.disconnectedStatus === '1' ? '#212121' : getDeviceModeColor(data.deviceMode),
                  height: '100%',
                }}
              />
            </div>
          </div>
        </div>
      </div>

    </div>
  );
}

export default DeviceWidget;
