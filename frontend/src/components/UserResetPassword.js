import React, { useState } from 'react';
import {
  Button,
  Card,
  CardHeader,
  CardContent,
  DialogContent,
  IconButton,
  InputAdornment,
  TextField,
  Typography,
  Divider,
} from '@mui/material';
import { Box } from '@mui/system';
import ForgotPassword from '../images/Forgot password.svg'
import { styled } from '@mui/material/styles';
import { useNavigate } from 'react-router-dom';
import { Visibility, VisibilityOff } from '@mui/icons-material';
import { PasswordResetService, LogoutService } from '../services/LoginPageService';
import { PasswordResetValidate } from '../validation/formValidation';
import ApplicationStore from '../utils/localStorageUtil';
import NotificationBar from './notification/ServiceNotificationBar';

function UserResetPassword(props) {
  const navigate = useNavigate();
  const [oldPassword, setOldPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showOldPassword, setShowOldpassword] = useState(false);
  const [showNewPassword, setShowNewpassword] = useState(false);
  const [errorObject, setErrorObject] = useState({});
  const [openNotification, setNotification] = useState({
    status: false,
    type: '',
    message: '',
  });

  const validateForNullValue = (value, type) => {
    PasswordResetValidate(value, type, setErrorObject);
  };

  const handleSuccess = (data) => {
    setNotification({
      status: true,
      type: 'success',
      message: 'Password has been successfully updated. Please relogin.',
    });
    setTimeout(() => {
      LogoutService(logoutSuccessCallback, logoutErrorCallBack);
    }, 2000);
  };

  const handleException = (errorStatus, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const logoutSuccessCallback = (data) => {
    // ApplicationStore().setStorage('userDetails', '');
    // ApplicationStore().setStorage('alertDetails', '');
    // ApplicationStore().setStorage('siteDetails', '');
    // ApplicationStore().setStorage('notificationDetails', '');
    ApplicationStore().clearStorage();
    navigate('/login');
  };

  const logoutErrorCallBack = (errorObject) => {
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (newPassword !== confirmPassword) {
      setErrorObject((oldData) => {
        const status = {
          errorStatus: true,
          helperText: 'Password do not match',
        };
        return {
          ...oldData,
          confirmPassword: status,
        };
      });
    }
    else if (oldPassword == newPassword) {
      setErrorObject((oldData) => {
        const status = {
          errorStatus: true,
          helperText: 'Old password and New password should be different',
        };
        return {
          ...oldData,
          newPassword: status,
        };
      });
    } else {
      PasswordResetService({ oldPassword, newPassword }, handleSuccess, handleException);
    }
  };

  const handleNotificationClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const resetForm = () => {
    setOldPassword('');
    setNewPassword('');
    setConfirmPassword('');
    setErrorObject({});
    setShowOldpassword(false);
    setShowNewpassword(false);
  };
  const Buttons = styled(Button)(
    () => ({
      color: 'white',
      padding: "8px 19px",
      marginTop: '10px',
      marginRight: '10px',
      marginBottom: '35px',
      fontSize: '13px',
      borderRadius: '10px',
      fontWeight: '600',
      fontFamily: 'customfont',
      letterSpacing: '1px',
    })
  )
  return (
    <div className={'p-5 mt-10 w-full'}>
      <Card className={'w-[400px] m-auto min-[320px]:w-full min-[768px]:w-[400px]'} style={{ borderRadius: '12px', boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px' }}>
        <CardHeader
          title={
            <>
              <img className='w-full h-[30vh]' src={ForgotPassword} alt="ForgotPassword" />
              <Typography
                sx={{
                  fontFamily: 'customfont',
                  fontWeight: '500',
                  fontSize: '20px',
                  letterSpacing: '1px',
                  color: 'black'
                }}
                variant="h5">
                Change Password
              </Typography>
            </>
          }
        />
        <CardContent>
          <form className="px-0 pt-0 pb-10 mb-0 ml-2 mt-0" onSubmit={handleSubmit}>
            <div className="mb-6 w-10/12 mr-auto ml-auto">
              <TextField
                margin="dense"
                id="outlined-required"
                label="Old Password"
                type={showOldPassword ? 'text' : 'password'}
                fullWidth
                value={oldPassword}
                required
                variant='standard'
                onBlur={() => {
                  validateForNullValue(oldPassword, 'oldPassword');
                  setShowOldpassword(false);
                }}
                onChange={(e) => {
                  setOldPassword(e.target.value);
                }}
                autoComplete="off"
                error={errorObject?.oldPassword?.errorStatus}
                helperText={errorObject?.oldPassword?.helperText}
                InputProps={{
                  style: { borderRadius: '7px', padding: '0 7px' },
                  endAdornment: <InputAdornment position="end">
                    <IconButton
                      aria-label="toggle password visibility"
                      onClick={(e) => {
                        setShowOldpassword(!showOldPassword);
                      }}
                      onMouseDown={(e) => { e.preventDefault(); }}
                      edge="end"
                    >
                      {showOldPassword ? <VisibilityOff /> : <Visibility />}
                    </IconButton>
                  </InputAdornment>,
                }}
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </div>
            <div className="mb-6 w-10/12 mr-auto ml-auto">
              <TextField
                id="dense"
                label="New Password"
                type={showNewPassword ? 'text' : 'password'}
                fullWidth
                value={newPassword}
                variant='standard'
                required
                onBlur={() => {
                  validateForNullValue(newPassword, 'newPassword');
                  setShowNewpassword(false);
                }}
                onChange={(e) => {
                  setNewPassword(e.target.value);
                }}
                autoComplete="off"
                error={errorObject?.newPassword?.errorStatus}
                helperText={errorObject?.newPassword?.helperText}
                InputProps={{
                  style: { borderRadius: '15px', padding: '0 7px' },
                  endAdornment: <InputAdornment position="end">
                    <IconButton
                      aria-label="toggle password visibility"
                      onClick={(e) => {
                        setShowNewpassword(!showNewPassword);
                      }}
                      onMouseDown={(e) => { e.preventDefault(); }}
                      edge="end"
                    >
                      {showNewPassword ? <VisibilityOff /> : <Visibility />}
                    </IconButton>
                  </InputAdornment>,
                }}
                InputLabelProps={{
                  shrink: true,
                }}
              />

            </div>
            <div className="mb-5 w-10/12 mr-auto ml-auto">
              <TextField
                id="dense"
                label="Confirm Password"
                type="password"
                fullWidth
                required
                variant='standard'
                value={confirmPassword}
                onBlur={() => validateForNullValue(confirmPassword, 'confirmPassword')}
                onChange={(e) => {
                  setConfirmPassword(e.target.value);
                }}
                autoComplete="off"
                error={errorObject?.confirmPassword?.errorStatus}
                helperText={errorObject?.confirmPassword?.helperText}
                InputProps={{
                  style: { borderRadius: '15px', padding: '0 7px' },
                }}
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </div>
            <div className="mt-3 ml-2 float-right mr-4 ">
              <Buttons
                style={{
                  background: 'rgb(19 60 129)',}}
                onClick={resetForm}
              >
                Cancel
              </Buttons>
              <Buttons
                style={{
                  background: 'rgb(19 60 129)',}}
                // disabled={
                //   errorObject?.confirmPassword?.errorStatus
                //   || errorObject?.newPassword?.errorStatus
                //   || errorObject?.oldPassword?.errorStatus
                // }
                type="submit"
              >
                Submit
              </Buttons>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}

export default UserResetPassword;
