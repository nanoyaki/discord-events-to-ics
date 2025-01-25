{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "1.0.0";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-SOtTKx20r92wxxtr9l0l08I+zbPoLAHhva/PpP4q1NU=";
}
