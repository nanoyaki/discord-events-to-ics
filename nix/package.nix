{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "0.3.0";

  src = ./..;

  php = php84.withExtensions (
    { all, enabled }:
    enabled
    ++ (with all; [
      mongodb
    ])
  );

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-2aCYVuHtdZHrgoOM/LWNwP2fF+gXyudiBSlY1TlQ15I=";
}
