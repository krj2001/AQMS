import {
  Dashboard,
  Group,
  BusinessOutlined,
  LockReset,
  Map,
  Storefront,
  BrowserUpdated,
  ChatBubbleOutline,
  Email,
  Groups,
  Diversity3,
} from "@mui/icons-material";
import DashboardCustomizeIcon from "@mui/icons-material/DashboardCustomize";
import PersonAddIcon from "@mui/icons-material/PersonAdd";
import AssessmentIcon from "@mui/icons-material/Assessment";
import DevicesIcon from "@mui/icons-material/Devices";
import ContactMailIcon from "@mui/icons-material/ContactMail";
import DevicesOtherIcon from "@mui/icons-material/DevicesOther";
import SummarizeIcon from "@mui/icons-material/Summarize";
import { Link } from "react-router-dom";

import { useEffect, useState } from "react";
import allowedSidebarItems from "../../utils/accessRoleUtil";
import ApplicationStore from "../../utils/localStorageUtil";
import defaultCompanyLogo from "../../images/defaultCompanyLogo.png";
// import defaultCompanyLogo from '../../images/Logo2.png';
// import companyLogos from '../../images/Logo2.png';

const SidebarItems = {
  "Dashboard Management": [
    {
      name: "Dashboard",
      route: "Dashboard",
      icon: (
        <DashboardCustomizeIcon
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
    {
      name: "User",
      route: "UserManagement",
      icon: (
        <PersonAddIcon
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
    {
      name: "Vendor",
      route: "Vendor",
      icon: (
        <Diversity3 className="sidebarIcon mr-2" style={{ fontSize: "16px" }} />
      ),
    },
    {
      name: "Gas Cylinder",
      route: "GasCylinder",
      icon: (
        <Storefront className="sidebarIcon mr-2" style={{ fontSize: "16px" }} />
      ),
    },
    {
      name: "Report",
      route: "Report",
      icon: (
        <AssessmentIcon
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
  ],
  "Customer Management": [
    {
      name: "Customer",
      route: "CustomerManagement",
      icon: (
        <BusinessOutlined
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
  ],
  "Profile Settings": [
    {
      name: "Change Password ",
      route: "ChangePassword",
      icon: (
        <LockReset className="sidebarIcon mr-2" style={{ fontSize: "16px" }} />
      ),
    },
    {
      name: "App Version ",
      route: "AppVersion",
      icon: (
        <BrowserUpdated
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
  ],
  "Configuration Management": [
    {
      name: "Device Config",
      route: "Location",
      icon: (
        <DevicesIcon
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
    {
      name: "Email Config",
      route: "EmailConfig",
      icon: (
        <ContactMailIcon
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
  ],
  "Device Management": [
    {
      name: "Devices",
      route: "Device",
      icon: (
        <DevicesOtherIcon
          className="sidebarIcon mr-2"
          style={{ fontSize: "16px" }}
        />
      ),
    },
  ],
};

function DrawerObject() {
  const allowedItems = allowedSidebarItems();
  const sectionCollection = {};
  for (const section in SidebarItems) {
    const allowedCollection = SidebarItems[section].filter(
      (item) => allowedItems.indexOf(item.route) > -1
    );

    if (allowedCollection.length > 0) {
      sectionCollection[section] = allowedCollection;
    }
  }

  const fetchSideBar = (sideBarObject) => {
    const returnObj = [];
    for (const section in sideBarObject) {
      returnObj.push(
        <div className="sidebarMenu mb-5" key={`${section}01`}>
          <h3 className="sidebarTitle text-sm font-semibold mt-1 mb-2 text-left ml-4 text-slate-500 ">
            {section}
          </h3>
          <ul className="sidebarList px-1 py-1">
            {sideBarObject[section].map((item, liIndex) => (
              <Link to={item.route} className="link" key={item.name + liIndex}>
                <li
                  className="sidebarListItem flex text-start text-xs cursor-pointer item-center px-3 py-3 mr-auto ml-auto rounded-lg transition-all hover:bg-slate-50"
                  title={item.name}
                >
                  {item.icon}
                  <span
                    className="text-sm ml-1 text-white font-medium "
                    style={{ color: "#252525" }}
                  >
                    {item.name}
                  </span>
                </li>
              </Link>
            ))}
          </ul>
        </div>
      );
    }
    return returnObj;
  };

  const [companyLogo, setCompanyLogo] = useState(defaultCompanyLogo);
  const { userDetails } = ApplicationStore().getStorage("userDetails");

  useEffect(() => {
    if (userDetails.companyLogo) {
      // setCompanyLogo(`http://wisething.in/Aqms/blog/public/${userDetails.companyLogo}?${new Date().getTime()}`);
      // setCompanyLogo(`http://localhost/backend/blog/public/${userDetails.companyLogo}`);
      setCompanyLogo(
        `http://localhost/aideaLabs/blog/public/${userDetails.companyLogo}`
      );
    }
  }, []);

  return (
    <div className="block">
      <div className="wrapper" style={{ display: "flex" }}>
        <div className="items">
          <div className="" style={{ backgroundColor: "white" }}>
            <Link to="Dashboard">
              <img
                src={companyLogo}
                alt="companyLogo"
                className="avatar w-10/12 h-full py-4 px-9 text-center"
              />
            </Link>
          </div>
        </div>
      </div>
      <div className="sidebar h-auto bg-white relative" style={{ top: 0 }}>
        <div
          className="sidebarWrapper py-4 px-3 text-center mr-auto ml-auto "
          style={{ color: "color: #555" }}
        >
          {fetchSideBar(sectionCollection)}
        </div>
      </div>
    </div>
  );
}

export { DrawerObject, SidebarItems };
