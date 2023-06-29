import { useState, useContext, useEffect } from "react";
import "./css/LoginPageComponent.css";
// import { LockClosedIcon } from '@heroicons/react/solid';
import { TextField, IconButton, InputAdornment } from "@mui/material";
import { Visibility, VisibilityOff } from "@mui/icons-material";
import LoadingButton from "@mui/lab/LoadingButton";
import { useNavigate } from "react-router-dom";
import loginPageWallpaper from "../images/loginimg.jpg";
// import logo from '../images/logo.svg';
import AuthContext from "../context/AuthProvider";
import { LoginService } from "../services/LoginPageService";
import ApplicationStore from "../utils/localStorageUtil";
import { LoginFormValidate } from "../validation/formValidation";
import NotificationBar from "../components/notification/ServiceNotificationBar";

import Logo1 from "../images/Logo1.svg";

function LoginPage() {
  const successCaseCode = [200, 201];
  const { setUserAuthetication } = useContext(AuthContext);
  const navigate = useNavigate();
  const [email, setUserEmail] = useState("");
  const [password, setUserPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [errorObject, setErrorObject] = useState({});
  const [openNotification, setNotification] = useState({
    status: false,
    type: "success",
    message: "Login Successful",
  });
  const [loading, setLoading] = useState(false);
  const DynamicLogo = ApplicationStore().getDynamicLogo();
  const companyName = ApplicationStore().getCompanyName();
  const customerImage = ApplicationStore().getCustomerImage();
  useEffect(() => {
    const { user_token, userDetails } =
      ApplicationStore().getStorage("userDetails");
    return user_token
      ? userDetails?.userRole === "superAdmin"
        ? navigate("/UserManagement")
        : userDetails?.secondLevelAuthorization === "true"
        ? navigate("/otp")
        : userDetails?.forcePasswordReset === 1
        ? navigate("/passwordReset")
        : navigate("/Dashboard")
      : {};
  }, []);

  const validateForNullValue = (value, type) => {
    LoginFormValidate(value, type, setErrorObject);
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: "",
      message: "",
    });
  };

  const onFormSubmit = async (event) => {
    event.preventDefault();
    setLoading(true);

    await LoginService({ email, password })
      .then((response) => {
        if (successCaseCode.indexOf(response.status) > 0) {
          setNotification({
            status: true,
            type: "success",
            message: "Login Success",
          });
          return response.json();
        }
        throw {
          errorStatus: response.status,
          errorObject: response.json(),
        };
      })
      .then((data) => {
        ApplicationStore().setStorage("userDetails", data);
        ApplicationStore().setStorage("navigateDashboard", {
          navigateDashboard: true,
        });
        ApplicationStore().setStorage("alertDetails", {
          locationIdList: [],
          branchIdList: [],
          facilityIdList: [],
          buildingIdList: [],
          floorIdList: [],
          labIdList: [],
          deviceIdList: [],
          sensorIdList: [],
        });
        ApplicationStore().setStorage("notificationDetails", {
          notificationList: [],
          newNotification: false,
        });
        ApplicationStore().setDynamicLogo(data.userDetails.companyLogo);
        ApplicationStore().setCompanyName(data.userDetails.companyName);
        ApplicationStore().setCustomerImage((typeof(data?.userDetails?.customerImage) === 'undefied' || typeof(data?.userDetails?.customerImage) === '') ? null  :  data?.userDetails?.customerImage);
        console.log("customerimage" + JSON.stringify(data.userDetails.customerImage));
        console.log(data.userDetails);

        if (data?.locationDetails) {
          var labelCount = 0;
          if (
            data?.locationDetails?.location_id !== null ||
            data?.locationDetails?.branch_id !== null ||
            data?.locationDetails?.facility_id !== null ||
            data?.locationDetails?.building_id !== null ||
            data?.locationDetails?.floor_id !== null ||
            data?.locationDetails?.lab_id !== null
          ) {
            labelCount =
              data?.locationDetails?.location_id !== null
                ? labelCount + 1
                : labelCount;
            labelCount =
              data?.locationDetails?.branch_id !== null
                ? labelCount + 1
                : labelCount;
            labelCount =
              data?.locationDetails?.facility_id !== null
                ? labelCount + 1
                : labelCount;
            labelCount =
              data?.locationDetails?.building_id !== null
                ? labelCount + 1
                : labelCount;
            labelCount =
              data?.locationDetails?.floor_id !== null
                ? labelCount + 1
                : labelCount;
            labelCount =
              data?.locationDetails?.lab_id !== null
                ? labelCount + 1
                : labelCount;

            ApplicationStore().setStorage("dashboardRefresh", {
              dashboardRefresh: true,
              labelCount: labelCount,
            });
          } else {
            ApplicationStore().setStorage("dashboardRefresh", {
              dashboardRefresh: false,
              labelCount: labelCount,
            });
          }
        }
        setUserAuthetication(data.response);
        setTimeout(() => {
          setLoading(false);
          if (data.userDetails.secondLevelAuthorization === "true") {
            navigate("/otp");
          } else if (data.userDetails.forcePasswordReset === 0) {
            data.userDetails.userRole === "superAdmin"
              ? navigate("/UserManagement")
              : navigate("/");
          } else {
            navigate("/passwordReset");
          }
        }, 3000);
      })
      .catch((error) => {
        setLoading(false);
        error?.errorObject?.then((errorResponse) => {
          setNotification({
            status: true,
            type: "error",
            message: errorResponse.error
              ? errorResponse.error
              : errorResponse.message,
          });
        });
      });
  };
  // let src;

  // if (customerImage !== null) {
  //   src = customerImage;
  // } else {
  //   src = loginPageWallpaper;
  // }
  return (
    <div className="grid grid-flow-row-dense h-screen w-full grid-cols-1 gap-y-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 items-center ">
      <div className="min-h-full flex items-center justify-center lg:px-1 ml-auto mr-auto w-9/12 bg-white h-full">
        <div className=" w-full space-y-0">
          <div className="left-1 top-3">
            <img
              className="mx-auto h-12"
              src={DynamicLogo != null ? DynamicLogo : Logo1}
              alt="Workflow"
            />
          </div>
          <h1 className="text-2xl py-3">
            Welcome to <span style={{ color: "#033882" }}>{companyName}</span>{" "}
          </h1>
          <h2 className="text-center text-sm font-sans font-bold text-gray-900 py-1 ">
            Sign in to your account
          </h2>

          <form
            className=" w-10/12 ml-auto mr-auto mt-2 space-y-6"
            onSubmit={onFormSubmit}
          >
            <div className="Inputtext rounded-md shadow-sm -space-y-px-0">
              <div className="mb-2 py-4">
                <TextField
                  id="standard-size-small"
                  label="Email Id"
                  type="email"
                  value={email}
                  size="small"
                  defaultValue="Small"
                  variant="standard"
                  // placeholder="Email address"
                  className="Logininput mb-2 appearance-none rounded-none relative block w-full border
                    border-gray-50 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-transparent
                    focus:ring-red-500 focus:border-red-500 text-xs sm:text-sm"
                  required
                  onBlur={() => validateForNullValue(email, "email")}
                  onChange={(e) => {
                    setUserEmail(e.target.value);
                  }}
                  InputLabelProps={{
                    style: { fontFamily: "customfont" },
                  }}
                  autoComplete="off"
                  error={errorObject?.emailId?.errorStatus}
                  helperText={errorObject?.emailId?.helperText}
                />
              </div>
              <div className="mt-2">
                <TextField
                  id="standard-basic"
                  label="Password"
                  type={showPassword ? "text" : "password"}
                  value={password}
                  variant="standard"
                  // placeholder="Password"
                  className="Logininput mt-2 flex-none appearance-none rounded-none relative block w-full py-2 border-hidden
                    border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none
                    focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm"
                  required
                  error={errorObject?.password?.errorStatus}
                  helperText={errorObject?.password?.helperText}
                  onBlur={() => {
                    validateForNullValue(password, "password");
                    setShowPassword(false);
                  }}
                  onChange={(e) => {
                    setUserPassword(e.target.value);
                  }}
                  InputLabelProps={{
                    style: { fontFamily: "customfont" },
                  }}
                  InputProps={{
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton
                          style={{
                            position: "absolute",
                            right: "5px",
                            top: "0",
                            bottom: "0px",
                          }}
                          aria-label="toggle password visibility"
                          onClick={(e) => {
                            setShowPassword(!showPassword);
                          }}
                          onMouseDown={(e) => {
                            e.preventDefault();
                          }}
                          edge="end"
                        >
                          {showPassword ? <VisibilityOff /> : <Visibility />}
                        </IconButton>
                      </InputAdornment>
                    ),
                  }}
                />
              </div>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <input
                  id="remember-me"
                  name="remember-me"
                  type="checkbox"
                  className=" h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label
                  htmlFor="remember-me"
                  className="ml-2 block text-sm text-black-900"
                >
                  Keep me logged in
                </label>
              </div>
              <div className="text-sm" />
            </div>
            <div>
              <LoadingButton
                type="submit"
                loading={loading}
                loadingPosition="end"
                variant="contained"
                className="group relative w-full flex justify-center py-2 px-4 border border-transparent
                  text-sm font-medium rounded-md text-black bg-white-500 hover:bg-red-100 focus:outline
                  focus:ring-2 focus:ring-offset-2 focus:ring-red-100 outline outline-offset-2 outline-2
                  outline-red-500"
                style={{
                  backgroundColor: "rgb(48 54 65)",
                  padding: "13px 0",
                  borderRadius: "50px",
                  color: "white",
                }}
              >
                <span className="absolute left-0 inset-y-0 flex items-center pl-3"></span>
                Sign in
              </LoadingButton>
              {/* <p className='py-4 text-xs'>Don't have an account? <span className='px-2 font-bold'> <a href='#'>Sign up for free</a></span></p> */}
            </div>
          </form>
        </div>
      </div>
      <div className="flex items-center justify-center hidden sm:block w-full bg-white">
        <div className="w-full space-y-0">
          <img
            className=" Loginimg object-cover flex item-right hidden sm:block h-screen"
            alt="login Page Wallpaper"
            src={customerImage != null &&  customerImage != '' ? customerImage : loginPageWallpaper}
            // src={loginPageWallpaper}
            style={{ filter: "brightness(60%)", objectFit:'cover' }}
          />
        </div>
        <div className="absolute bottom-5 left-auto right-auto px-5 text-left text-5xl text-white font-bold leading-tight">
          {/* <p>
            <span>"</span>A smile
          </p>
          <p> is a welcomed sight</p>
          <p>
            that invites people in.<span>"</span>
          </p> */}
        </div>
      </div>
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </div>
  );
}

export default LoginPage;
